// Local PostCSS config for the React/Inertia pilot (Mantine).
// Also stops Vite from walking UP the directory tree and picking up the stray
// C:\OmegaLife\postcss.config.js that belongs to another project.
module.exports = {
  plugins: {
    'postcss-preset-mantine': {},
    'postcss-simple-vars': {
      variables: {
        'mantine-breakpoint-xs': '36em',
        'mantine-breakpoint-sm': '48em',
        'mantine-breakpoint-md': '62em',
        'mantine-breakpoint-lg': '75em',
        'mantine-breakpoint-xl': '88em',
      },
    },
  },
};
