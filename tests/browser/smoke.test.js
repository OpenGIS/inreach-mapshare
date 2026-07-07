import { test, expect } from "@playwright/test";
import { readFileSync } from "fs";
import { resolve } from "path";

test.describe("inReach MapShare smoke", () => {
  test("front page loads without JS errors", async ({ page }) => {
    const errors = [];
    page.on("console", (msg) => msg.type() === "error" && errors.push(msg.text()));
    page.on("pageerror", (err) => errors.push(err.message));

    await page.goto("/", { waitUntil: "networkidle" });
    expect(errors).toEqual([]);
  });

  test("create-map-instance module has no syntax errors", async ({ page }) => {
    const errors = [];
    page.on("console", (msg) => msg.type() === "error" && errors.push(msg.text()));
    page.on("pageerror", (err) => errors.push(err.message));

    await page.goto("/", { waitUntil: "load" });

    // Load the module as a script to check for syntax errors
    const modulePath = resolve("dist/create-map-instance.js");
    const moduleContent = readFileSync(modulePath, "utf-8");

    await page.addScriptTag({ content: moduleContent, type: "module" });

    // Wait a beat for any async errors
    await page.waitForTimeout(500);

    // Check no errors from loading the script
    const jsErrors = errors.filter(
      (e) =>
        !e.includes("favicon") &&
        !e.includes("net::ERR_") &&
        !e.includes("Failed to load resource"),
    );
    expect(jsErrors).toEqual([]);
  });
});
