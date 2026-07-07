import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: "./tests/browser",
  timeout: 30000,
  use: {
    baseURL: "http://localhost:8888",
    headless: true,
    screenshot: "only-on-failure",
  },
  webServer: {
    command: "npm run dev",
    url: "http://localhost:8888",
    reuseExistingServer: true,
  },
});
