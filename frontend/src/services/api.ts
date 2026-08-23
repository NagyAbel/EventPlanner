import router from '@/router'

let csrfPromise: Promise<void> | null = null

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'))
  const value = match?.[2]
  return value ? decodeURIComponent(value) : null
}

async function getCsrfCookie(force = false): Promise<void> {
  if (csrfPromise && !force) {
    return csrfPromise
  }

  csrfPromise = fetch('/sanctum/csrf-cookie', {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      if (!response.ok) throw new Error('CSRF init failed')
    })
    .catch((err) => {
      csrfPromise = null
      throw err
    })

  return csrfPromise
}

function resetCsrfCookie() {
  csrfPromise = null
}
function handleUnauthorized() {
  window.dispatchEvent(new Event('auth:unauthorized'))
  if (router.currentRoute.value.path !== '/auth') {
    router.push({
      path: '/auth',
      query: { redirect: router.currentRoute.value.fullPath }
    })
  }
}

async function performRequest(endpoint: string, options: RequestInit = {}): Promise<Response> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (!(options.body instanceof FormData)) {
    headers.set('Content-Type', 'application/json')
  }

  // Attach Sanctum CSRF header if cookie exists
  const xsrfToken = getCookie('XSRF-TOKEN')
  if (xsrfToken) {
    headers.set('X-XSRF-TOKEN', xsrfToken)
  }

  return fetch(endpoint, {
    ...options,
    credentials: 'include',
    headers,
  })
}

async function parseResponse<T>(response: Response): Promise<T> {
  const data = await response.json().catch(() => null)
  if (!response.ok) {
    throw new Error(data?.message ?? 'Something went wrong.')
  }
  return data
}

export async function apiRequest<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const response = await performRequest(endpoint, options)
  return parseResponse<T>(response)
}

export async function authRequest<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  await getCsrfCookie()

  let response = await performRequest(endpoint, options)

  if (response.status === 419) {
    resetCsrfCookie()
    await getCsrfCookie(true)
    response = await performRequest(endpoint, options)
  }

  if (response.status === 401) {
    window.dispatchEvent(new Event('auth:unauthorized'))
    throw new Error('Your session has expired.')
  }

  return parseResponse<T>(response)
}
window.addEventListener('auth:unauthorized', handleUnauthorized)