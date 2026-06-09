import { Alert } from '@mantine/core';
import { useFlash } from '@frontend/hooks/useFlash';

/**
 * FlashAlerts — renders the Laravel success/error flash messages.
 * Drop-in replacement for the Alert block that was copy-pasted across pages:
 *
 *   <FlashAlerts />
 *
 * Any extra props (e.g. `mb`) are forwarded to the underlying Mantine Alert.
 */
export default function FlashAlerts({ mb = 'md', radius = 'md', ...rest }) {
    const flash = useFlash();
    return (
        <>
            {flash.success && <Alert color="green" mb={mb} radius={radius} {...rest}>{flash.success}</Alert>}
            {flash.error && <Alert color="red" mb={mb} radius={radius} {...rest}>{flash.error}</Alert>}
        </>
    );
}
