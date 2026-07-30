/**
 * PlantGuardian API Client
 * Centralized fetch() wrapper for all backend communication.
 * Rujuk docs/architecture.md §2.3
 */

const API_BASE_URL = '/api';

class ApiClient {
  constructor() {
    this.baseUrl = API_BASE_URL;
  }

  /**
   * Helper to get CSRF token from meta tag if available
   */
  getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  /**
   * Default headers for JSON requests
   */
  getHeaders(isFormData = false) {
    const headers = {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': this.getCsrfToken(),
    };

    if (!isFormData) {
      headers['Content-Type'] = 'application/json';
    }

    return headers;
  }

  /**
   * Parse HTTP response cleanly
   */
  async handleResponse(response) {
    const isJson = response.headers.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
      const errorMessage = data?.message || data?.error || `HTTP error ${response.status}`;
      const error = new Error(errorMessage);
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  }

  /**
   * GET Request
   */
  async get(endpoint, params = {}) {
    const url = new URL(`${this.baseUrl}${endpoint}`, window.location.origin);
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined) {
        url.searchParams.append(key, params[key]);
      }
    });

    const response = await fetch(url.toString(), {
      method: 'GET',
      headers: this.getHeaders(),
      credentials: 'include',
    });

    return this.handleResponse(response);
  }

  /**
   * POST Request (JSON or FormData)
   */
  async post(endpoint, body = {}, isFormData = false) {
    const url = `${this.baseUrl}${endpoint}`;
    const options = {
      method: 'POST',
      headers: this.getHeaders(isFormData),
      credentials: 'include',
      body: isFormData ? body : JSON.stringify(body),
    };

    const response = await fetch(url, options);
    return this.handleResponse(response);
  }

  /**
   * PUT Request (JSON or FormData)
   */
  async put(endpoint, body = {}, isFormData = false) {
    const url = `${this.baseUrl}${endpoint}`;
    const options = {
      method: 'PUT',
      headers: this.getHeaders(isFormData),
      credentials: 'include',
      body: isFormData ? body : JSON.stringify(body),
    };

    const response = await fetch(url, options);
    return this.handleResponse(response);
  }

  /**
   * DELETE Request
   */
  async delete(endpoint) {
    const url = `${this.baseUrl}${endpoint}`;
    const response = await fetch(url, {
      method: 'DELETE',
      headers: this.getHeaders(),
      credentials: 'include',
    });

    return this.handleResponse(response);
  }
}

export const apiClient = new ApiClient();
export default apiClient;
