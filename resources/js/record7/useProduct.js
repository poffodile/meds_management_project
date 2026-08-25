import { usePage } from '@inertiajs/react';

/**
 * Product identity, shared onto every page by the server.
 *
 * The build plan requires the name to be changeable from configuration, so no
 * component may hard-code it. This is the only way to read it.
 */
export default function useProduct() {
    const { product } = usePage().props;

    return {
        name: product?.name ?? 'Record7',
        strapline: product?.strapline ?? '',
        seventhRight: product?.seventhRight ?? 'The Right Record',
    };
}
