import { defineConfig } from "vite";

// https://vitejs.dev/config/
export default defineConfig({
	build: {
		// copyPublicDir: false,
		// manifest: true,
		rollupOptions: {
			input: "main.js",
			output: {
				entryFileNames: "inreach-mapshare.js",
				chunkFileNames: "inreach-mapshare.js",
				assetFileNames: "inreach-mapshare.[ext]",
			},
		},
	},
});
