/**
 * Манифест публичных шаблонов и блоков.
 *
 * Ключ — имя компонента из реестра PageTemplates (например "templates/Home"),
 * значение — Vue-компонент. Состав секций задаётся в коде шаблона,
 * данные приходят из пропсов Inertia (page, acf, seo, settings).
 */
const templateModules = import.meta.glob('./*.vue', { eager: true })
const blockModules = import.meta.glob('./blocks/*.vue', { eager: true })

const components = {}

const register = (modules, prefix) => {
    for (const [path, module] of Object.entries(modules)) {
        const name = path.replace(/^\.\//, '').replace(/\.vue$/, '')
        components[`${prefix}${name}`] = module.default
    }
}

register(templateModules, 'templates/')
register(blockModules, 'templates/blocks/')

export function resolveTemplateComponent (name) {
    return components[name] ?? null
}

export default components