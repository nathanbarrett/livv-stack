import _axios, {AxiosInstance, InternalAxiosRequestConfig} from "axios";

const axios: AxiosInstance = _axios.create();

/**
 * meta tag with csrf token is loaded dynamically through Inertia -> Vue so it may not be available for axios calls
 * that occur before all attributes are set
 * */
function resolveCsrfToken(): Promise<string> {
    return new Promise((resolve, reject) => {
        const metaEl = document.querySelector('meta[name="csrf-token"]');
        const content = metaEl?.getAttribute('content');

        if (content) {
            return resolve(content);
        }

        const start = Date.now();
        const maxWait = 3000;
        const intervalToken = setInterval(() => {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const tokenContent = tokenEl?.getAttribute('content');

            if (tokenContent) {
                clearInterval(intervalToken);
                return resolve(tokenContent);
            }

            if (Date.now() - start > maxWait) {
                clearInterval(intervalToken);
                reject('CSRF token not found');
            }
        }, 100);
    });
}

async function requestInterceptor(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> {
    config.headers['X-Requested-With'] = 'XMLHttpRequest';
    config.headers['X-CSRF-TOKEN'] = await resolveCsrfToken();

    return config;
}

axios.interceptors.request.use(requestInterceptor);
export default axios;
