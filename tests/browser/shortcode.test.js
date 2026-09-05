import { test, expect } from "@playwright/test";
import { execSync } from "child_process";
import { fileURLToPath } from "url";
import { dirname, resolve } from "path";

const REPO_ROOT = resolve(dirname(fileURLToPath(import.meta.url)), "../..");

test.describe("inReach MapShare shortcode", () => {
  test.setTimeout(60000);

  let postId;

  test.beforeAll(() => {
    try {
      postId = execSync(
        'npx wp-env run cli wp post create --post_title="MapShare Demo" --post_content="[inreach-mapshare mapshare_identifier=\\"demo\\"]" --post_status=publish --porcelain',
        { cwd: REPO_ROOT, encoding: "utf-8" },
      ).trim();
    } catch (err) {
      throw new Error("Failed to create demo post (is wp-env running?): " + err.message);
    }
  });

  test.afterAll(() => {
    if (postId) {
      try {
        execSync(`npx wp-env run cli wp post delete ${postId} --force`, {
          cwd: REPO_ROOT,
          stdio: "ignore",
        });
      } catch {
        // Best-effort cleanup — swallow errors.
      }
    }
  });

  test("renders a working map with demo data", async ({ page }) => {
    const errors = [];
    const warnings = [];

    page.on("console", (msg) => {
      if (msg.type() === "error") errors.push(msg.text());
      if (msg.type() === "warning" && msg.text().includes("READ-usage")) warnings.push(msg.text());
    });
    page.on("pageerror", (err) => errors.push(err.message));

    await page.goto(`/?p=${postId}`, { waitUntil: "load" });

    await expect(page.locator(".inmap-map")).toHaveCount(1);
    await expect(page.locator("canvas.maplibregl-canvas")).toBeVisible({ timeout: 30000 });
    await expect(page.locator(".maplibregl-popup")).toBeVisible({ timeout: 30000 });
    await expect(page.locator("table.waymark-feature-props")).toBeVisible();

    const scrollYAfterPopup = await page.evaluate(() => window.scrollY);
    await page.waitForTimeout(2000);
    const scrollYAfterWait = await page.evaluate(() => window.scrollY);
    expect(scrollYAfterPopup).toBe(0);
    expect(scrollYAfterWait).toBe(0);

    const jsErrors = errors.filter(
      (e) =>
        !e.includes("favicon") &&
        !e.includes("net::ERR_") &&
        !e.includes("Failed to load resource"),
    );
    expect(jsErrors).toEqual([]);
    expect(warnings).toEqual([]);

    await expect(page.locator("body")).not.toContainText(
      "Unable to read Demo KML",
    );
    await expect(page.locator("body")).not.toContainText(
      "No tracking or route data",
    );
  });
});