import { expect, test } from '@playwright/test';

test('sample applications can be filtered, inspected, and corrected', async ({ page }, testInfo) => {
    const errors: string[] = [];
    page.on('pageerror', (e) => errors.push(e.message));
    await page.goto('/demo');
    await expect(page.getByRole('heading', { name: 'Your next chapter starts here.' })).toBeVisible();
    await expect(page.getByText('Sample workspace', { exact: true })).toBeVisible();
    await page.getByRole('textbox', { name: 'Search company or role' }).fill('Northstar');
    await page.getByRole('button', { name: 'Search', exact: true }).click();
    await expect(page.locator('tbody tr')).toHaveCount(1);
    if ((page.viewportSize()?.width || 1440) <= 600) await page.locator('.mobile-application').filter({ hasText: 'Northstar' }).click();
    else await page.getByRole('link', { name: 'View Northstar' }).click();
    await expect(page.getByRole('heading', { name: 'Email timeline' })).toBeVisible();
    await page.getByRole('combobox', { name: 'Status', exact: true }).selectOption('offer');
    await page.getByRole('button', { name: 'Save correction' }).click();
    await expect(page.getByRole('status')).toContainText('Application updated');
    await page.reload();
    await expect(page.getByRole('combobox', { name: 'Status', exact: true })).toHaveValue('offer');
    await page.getByRole('combobox', { name: 'Status', exact: true }).selectOption('');
    await page.getByRole('button', { name: 'Save correction' }).click();
    await page.getByRole('link', { name: 'All applications' }).click();
    await page.screenshot({ path: `test-results/pegasus-${testInfo.project.name}.png`, fullPage: true });
    await expect(page.getByRole('heading', { name: 'Application activity' })).toBeVisible();
    expect(errors).toEqual([]);
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    await page.getByRole('link', { name: 'Manage mailboxes' }).click();
    await expect(page.getByRole('heading', { name: 'A home for every mailbox.' })).toBeVisible();
});
