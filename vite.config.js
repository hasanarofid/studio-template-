import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'build',
    emptyOutDir: true,
  },
  base: './', // Use relative paths for assets on shared hosting
});
