import axios, { AxiosInstance, AxiosError } from 'axios'
import type {
    User,
    Seller,
    Sale,
    LoginRequest,
    RegisterRequest,
    AuthResponse,
    SellerRequest,
    SaleRequest,
    ApiResponse,
    ApiError
} from '@/types'

class ApiService {
    private api: AxiosInstance

    constructor() {
        this.api = axios.create({
            baseURL: 'http://localhost:8080/api',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })

        this.api.interceptors.request.use((config) => {
            const token = localStorage.getItem('token')
            if (token) {
                config.headers.Authorization = `Bearer ${token}`
            }
            return config
        })

        this.api.interceptors.response.use(
            (response) => response,
            (error: AxiosError<ApiError>) => {
                if (error.response?.status === 401) {
                    // Token inválido ou expirado
                    localStorage.removeItem('token')
                    window.location.href = '/login'
                }
                return Promise.reject(error)
            }
        )
    }

    // Auth endpoints
    async register(data: RegisterRequest): Promise<AuthResponse> {
        const response = await this.api.post<AuthResponse>('/register', data)
        return response.data
    }

    async login(data: LoginRequest): Promise<AuthResponse> {
        const response = await this.api.post<AuthResponse>('/login', data)
        return response.data
    }

    async logout(): Promise<void> {
        await this.api.post('/logout')
        localStorage.removeItem('token')
    }

    async getMe(): Promise<ApiResponse<User>> {
        const response = await this.api.get<ApiResponse<User>>('/me')
        return response.data
    }

    // Sellers endpoints
    async getSellers(): Promise<ApiResponse<Seller[]>> {
        const response = await this.api.get<ApiResponse<Seller[]>>('/sellers')
        return response.data
    }

    async createSeller(data: SellerRequest): Promise<ApiResponse<Seller>> {
        const response = await this.api.post<ApiResponse<Seller>>('/sellers', data)
        return response.data
    }

    // Sales endpoints
    async getSales(): Promise<ApiResponse<Sale[]>> {
        const response = await this.api.get<ApiResponse<Sale[]>>('/sales')
        return response.data
    }

    async createSale(data: SaleRequest): Promise<ApiResponse<Sale>> {
        const response = await this.api.post<ApiResponse<Sale>>('/sales', data)
        return response.data
    }

    async getSalesBySeller(sellerId: number): Promise<ApiResponse<Sale[]>> {
        const response = await this.api.get<ApiResponse<Sale[]>>(`/sellers/${sellerId}/sales`)
        return response.data
    }
}

export default new ApiService()
