import axios from 'axios';

const client = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: { Accept: 'application/json' },
});

client.interceptors.response.use(
    (r) => r,
    (err) => {
        if (err.response?.status === 419) window.location.reload();
        return Promise.reject(err);
    },
);

export default client;

export async function ensureCsrf(): Promise<void> {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}
