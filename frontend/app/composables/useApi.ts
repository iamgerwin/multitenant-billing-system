import type { ApiErrorResponse } from '@billing/shared'

interface FetchOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined>
}

export function useApi() {
  const config = useRuntimeConfig()
  const baseUrl = config.public.apiBaseUrl as string

  const getToken = (): string | null => {
    if (import.meta.client) {
      return localStorage.getItem('auth_token')
    }
    return null
  }

  const buildUrl = (endpoint: string, params?: Record<string, string | number | boolean | undefined>): string => {
    const url = new URL(`${baseUrl}${endpoint}`)
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined) {
          url.searchParams.append(key, String(value))
        }
      })
    }
    return url.toString()
  }

  const getHeaders = (customHeaders?: HeadersInit): HeadersInit => {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    const token = getToken()
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    return { ...headers, ...(customHeaders as Record<string, string>) }
  }

  const handleResponse = async <T>(response: Response): Promise<T> => {
    if (!response.ok) {
      const error: ApiErrorResponse = await response.json().catch(() => ({
        message: 'An unexpected error occurred',
      }))

      if (response.status === 401) {
        if (import.meta.client) {
          localStorage.removeItem('auth_token')
          localStorage.removeItem('auth_user')
          navigateTo('/login')
        }
      }

      throw error
    }

    return response.json()
  }

  const get = async <T>(endpoint: string, options?: FetchOptions): Promise<T> => {
    const { params, ...fetchOptions } = options || {}
    const url = buildUrl(endpoint, params)

    const response = await fetch(url, {
      method: 'GET',
      headers: getHeaders(fetchOptions.headers),
      ...fetchOptions,
    })

    return handleResponse<T>(response)
  }

  const post = async <T>(endpoint: string, data?: unknown, options?: FetchOptions): Promise<T> => {
    const { params, ...fetchOptions } = options || {}
    const url = buildUrl(endpoint, params)

    const response = await fetch(url, {
      method: 'POST',
      headers: getHeaders(fetchOptions.headers),
      body: data ? JSON.stringify(data) : undefined,
      ...fetchOptions,
    })

    return handleResponse<T>(response)
  }

  const put = async <T>(endpoint: string, data?: unknown, options?: FetchOptions): Promise<T> => {
    const { params, ...fetchOptions } = options || {}
    const url = buildUrl(endpoint, params)

    const response = await fetch(url, {
      method: 'PUT',
      headers: getHeaders(fetchOptions.headers),
      body: data ? JSON.stringify(data) : undefined,
      ...fetchOptions,
    })

    return handleResponse<T>(response)
  }

  const patch = async <T>(endpoint: string, data?: unknown, options?: FetchOptions): Promise<T> => {
    const { params, ...fetchOptions } = options || {}
    const url = buildUrl(endpoint, params)

    const response = await fetch(url, {
      method: 'PATCH',
      headers: getHeaders(fetchOptions.headers),
      body: data ? JSON.stringify(data) : undefined,
      ...fetchOptions,
    })

    return handleResponse<T>(response)
  }

  const del = async <T>(endpoint: string, options?: FetchOptions): Promise<T> => {
    const { params, ...fetchOptions } = options || {}
    const url = buildUrl(endpoint, params)

    const response = await fetch(url, {
      method: 'DELETE',
      headers: getHeaders(fetchOptions.headers),
      ...fetchOptions,
    })

    return handleResponse<T>(response)
  }

  return {
    get,
    post,
    put,
    patch,
    del,
  }
}
