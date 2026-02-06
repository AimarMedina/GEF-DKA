
import { createApp } from 'vue'
import { createPinia } from 'pinia'

// Import global styles (includes custom-bootstrap that overrides Bootstrap variables)
import './styles.scss'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
