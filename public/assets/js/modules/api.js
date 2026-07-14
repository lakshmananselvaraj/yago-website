/* =========================================================================
   Vipasa Yoga — API client
   Tiny fetch wrapper for same-origin /api/* JSON endpoints.
   ========================================================================= */

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export async function apiRequest(method, path, body) {
  const upperMethod = method.toUpperCase();
  const headers = {};
  const init = {
    method: upperMethod,
    credentials: 'same-origin',
    headers,
  };

  if (upperMethod !== 'GET' && upperMethod !== 'HEAD') {
    headers['X-CSRF-Token'] = getCsrfToken();
  }

  if (body !== undefined) {
    headers['Content-Type'] = 'application/json';
    init.body = JSON.stringify(body);
  }

  const response = await fetch(path, init);

  let data = null;
  try {
    data = await response.json();
  } catch {
    data = null;
  }

  if (!response.ok) {
    const message = data?.message ?? `Request failed with status ${response.status}`;
    const error = new Error(message);
    error.status = response.status;
    error.errors = data?.errors ?? null;
    // Some endpoints embed extra top-level fields on failure (e.g. Controller::fail()'s
    // $data argument, such as a `redirect` hint on the "please verify" 403 response).
    // Expose the full parsed payload so callers can read those without re-fetching.
    error.data = data;
    throw error;
  }

  return data;
}

export const apiGet = (path) => apiRequest('GET', path);
export const apiPost = (path, body) => apiRequest('POST', path, body);
export const apiPut = (path, body) => apiRequest('PUT', path, body);
