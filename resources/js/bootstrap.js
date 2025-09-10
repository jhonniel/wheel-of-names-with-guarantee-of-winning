import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const tokenTag = document.head.querySelector('meta[name="csrf-token"]');
if (tokenTag) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenTag.content;
}
