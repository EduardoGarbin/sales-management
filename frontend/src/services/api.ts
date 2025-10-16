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
    ApiError,
    PaginatedResponse
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
    async getSellers(page: number = 1, perPage: number = 15): Promise<PaginatedResponse<Seller>> {
        const response = await this.api.get<PaginatedResponse<Seller>>('/sellers', {
            params: { page, per_page: perPage }
        })
        return response.data
    }

    async createSeller(data: SellerRequest): Promise<ApiResponse<Seller>> {
        const response = await this.api.post<ApiResponse<Seller>>('/sellers', data)
        return response.data
    }

    // Sales endpoints
    async getSales(page: number = 1, perPage: number = 15): Promise<PaginatedResponse<Sale>> {
        const response = await this.api.get<PaginatedResponse<Sale>>('/sales', {
            params: { page, per_page: perPage }
        })
        return response.data
    }

    async createSale(data: SaleRequest): Promise<ApiResponse<Sale>> {
        const response = await this.api.post<ApiResponse<Sale>>('/sales', data)
        return response.data
    }

    async getSalesBySeller(sellerId: number, page: number = 1, perPage: number = 15): Promise<PaginatedResponse<Sale>> {
        const response = await this.api.get<PaginatedResponse<Sale>>(`/sellers/${sellerId}/sales`, {
            params: { page, per_page: perPage }
        })
        return response.data
    }

    async resendCommissionEmail(sellerId: number, date: string): Promise<ApiResponse<any>> {
        const response = await this.api.post<ApiResponse<any>>(`/sellers/${sellerId}/resend-commission-email`, { date })
        return response.data
    }
}

export default new ApiService()
