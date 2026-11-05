import AcfField from './components/AcfField.vue'
import AcfFieldGroup from './components/AcfFieldGroup.vue'
import AcfRepeater from './components/fields/AcfRepeater.vue'
import './acf.css'

const components = { AcfField, AcfFieldGroup, AcfRepeater }

/**
 * Vue-плагин пакета teo/acf.
 * Подключение:  import Acf from '@acf';  app.use(Acf, { apiPrefix, mediaEndpoint })
 */
export default {
    install(app, options = {}) {
        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component)
        })

        app.provide('acf', {
            apiPrefix: options.apiPrefix ?? '/api/admin/acf',
            mediaEndpoint: options.mediaEndpoint ?? '/api/admin/media',
            ...options,
        })
    },
}

export { AcfField, AcfFieldGroup, AcfRepeater }
export { useAcfGroups } from './composables/useAcfGroups'
export { useAcfValues } from './composables/useAcfValues'
