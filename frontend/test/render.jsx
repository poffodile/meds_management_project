import { render } from '@testing-library/react';
import { MantineProvider } from '@mantine/core';
import { theme } from '@frontend/theme';

/**
 * Render a component wrapped in MantineProvider (needed for any Mantine UI).
 * Use this in tests instead of @testing-library's render directly.
 */
export function renderWithMantine(ui) {
    return render(<MantineProvider theme={theme}>{ui}</MantineProvider>);
}
