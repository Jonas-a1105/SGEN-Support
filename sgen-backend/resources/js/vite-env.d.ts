/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent<{}, {}, any>;
    export default component;
}

declare module 'ziggy-js' {
    export const ZiggyVue: any;
    export function route(name?: string, params?: any, absolute?: boolean, customZiggy?: any): any;
}
