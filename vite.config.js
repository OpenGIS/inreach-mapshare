import { defineConfig } from "vite";
import { viteStaticCopy } from "vite-plugin-static-copy";

// https://vitejs.dev/config/
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        "inreach-mapshare-admin": "./src/main.js",
        "create-map-instance": "./src/js/create-map-instance.js",
      },
      preserveEntrySignatures: "allow-extension",
      output: {
        entryFileNames: "[name].js",
        chunkFileNames: "[name].js",
        assetFileNames: "inreach-mapshare.[ext]",
      },
    },
  },
  plugins: [
    viteStaticCopy({
      targets: [
        { src: "waymark-js/dist/waymark.js", dest: "." },
        { src: "waymark-js/dist/waymark.css", dest: "." },
      ],
    }),
  ],
});
