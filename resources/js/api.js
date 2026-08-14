(() => {
    const appConfig = window.__APP_CONFIG__ || {};
    const baseUrl = String(appConfig.apiBaseUrl || '').replace(/\/+$/, '');

    const getBearerToken = () => {
        try {
            const session = window.HimsSession?.read?.();
            if (session?.token) {
                return session.token;
            }

            return sessionStorage.getItem('himsMainSessionToken') || '';
        } catch {
            return '';
        }
    };

    const buildUrl = (path = '') => {
        if (!path) return path;
        if (/^https?:\/\//i.test(path)) return path;
        const normalizedPath = path.startsWith('/') ? path : `/${path}`;
        return `${baseUrl}${normalizedPath}` || normalizedPath;
    };

    const request = async (path, options = {}) => {
        const method = String(options.method || 'GET').toUpperCase();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const rawBody = Object.prototype.hasOwnProperty.call(options, 'body') ? options.body : undefined;
        const isFormData = typeof FormData !== 'undefined' && rawBody instanceof FormData;
        const authToken = getBearerToken();

        const headers = {
            Accept: 'application/json',
            ...(options.headers || {}),
        };

        if (csrfToken && !headers['X-CSRF-TOKEN']) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        if (authToken && !headers.Authorization) {
            headers.Authorization = `Bearer ${authToken}`;
        }

        if (!isFormData && rawBody !== undefined && rawBody !== null && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(buildUrl(path), {
            credentials: 'same-origin',
            method,
            ...options,
            headers,
            body: isFormData || rawBody === undefined || rawBody === null
                ? rawBody
                : typeof rawBody === 'string'
                    ? rawBody
                    : JSON.stringify(rawBody),
        });

        let payload = {};
        try {
            payload = await response.json();
        } catch {
            payload = {};
        }

        if (!response.ok) {
            throw new Error(payload?.message || payload?.error || 'Request failed.');
        }

        return payload;
    };

    const get = (path, options = {}) => request(path, { ...options, method: 'GET' });
    const post = (path, body, options = {}) => request(path, { ...options, method: 'POST', body });
    const put = (path, body, options = {}) => request(path, { ...options, method: 'PUT', body });
    const patch = (path, body, options = {}) => request(path, { ...options, method: 'PATCH', body });
    const del = (path, options = {}) => request(path, { ...options, method: 'DELETE' });

    window.HimsApi = Object.freeze({
        buildUrl,
        request,
        get,
        post,
        put,
        patch,
        del,
    });
})();
