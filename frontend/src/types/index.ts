// User types
export interface User {
    id: number
    name: string
    email: string
    created_at: string
    updated_at: string
}

export interface LoginRequest {
    email: string
    password: string
}

export interface RegisterRequest {
    name: string
    email: string
    password: string
    password_confirmation: string
}

export interface AuthResponse {
    user: User
    token: string
}

// Seller types
export interface Seller {
    id: number
    name: string
    email: string
    commission_rate?: number
    created_at: string
    updated_at: string
}

export interface SellerRequest {
    name: string
    email: string
}

// Sale types
export interface Sale {
    id: number
    seller: Seller
    amount: string
    commission: string
    sale_date: string
    created_at: string
    updated_at: string
}

export interface SaleRequest {
    seller_id: number
    amount: number
    sale_date: string
}

// API Response types
export interface ApiResponse<T> {
    data: T
    message?: string
}

export interface ApiError {
    message: string
    errors?: Record<string, string[]>
}
