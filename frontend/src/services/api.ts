async function getCsrfCookie() {
  const response = await fetch('/sanctum/csrf-cookie', {
    method: 'GET',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  })

  if (!response.ok) {
    throw new Error('Unable to initialize authentication.')
  }
}

export async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {},
): Promise<T> {
  const response = await fetch(endpoint, {
    ...options,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    throw new Error(data?.message ?? 'Something went wrong.')
  }

  return data
}

export async function authRequest<T>(
  endpoint: string,
  options: RequestInit = {},
): Promise<T> {
  await getCsrfCookie()

  return apiRequest<T>(endpoint, options)
}