import { describe, it, expect } from 'vitest';
import { Account } from './Account';
import { Money } from './Money';

describe('Account Domain Entity', () => {
    it('creates account with id, balance, and date', () => {
        const date = new Date('2026-07-28');
        const account = new Account(1, Money.of(500), date);

        expect(account.id).toBe(1);
        expect(account.balance.amount).toBe(500);
        expect(account.createdAt).toBe(date);
    });

    it('fromData maps API response correctly', () => {
        const dateStr = '2026-07-28T12:00:00.000Z';
        const account = Account.fromData({
            id: 42,
            balance: 1500,
            created_at: dateStr,
        });

        expect(account.id).toBe(42);
        expect(account.balance.amount).toBe(1500);
        expect(account.createdAt.toISOString()).toBe(dateStr);
    });

    it('fromData handles zero balance', () => {
        const account = Account.fromData({
            id: 1,
            balance: 0,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.balance.amount).toBe(0);
        expect(account.balance.isPositiveOrZero()).toBe(true);
    });

    it('fromData handles negative balance', () => {
        const account = Account.fromData({
            id: 1,
            balance: -200,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.balance.amount).toBe(-200);
        expect(account.balance.isNegative()).toBe(true);
    });

    it('fromData handles very large balance', () => {
        const account = Account.fromData({
            id: 1,
            balance: 9_999_999_999,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.balance.amount).toBe(9_999_999_999);
    });

    it('fromData handles ISO date string', () => {
        const account = Account.fromData({
            id: 1,
            balance: 100,
            created_at: '2026-07-28T10:30:00.000Z',
        });

        expect(account.createdAt.getTime()).toBeGreaterThan(0);
    });

    it('is immutable - readonly properties', () => {
        const account = Account.fromData({
            id: 1,
            balance: 100,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        // Properties are readonly, but balance object is mutable internally
        // Verify balance operations return new instances
        const newBalance = account.balance.plus(Money.of(50));
        expect(account.balance.amount).toBe(100);
        expect(newBalance.amount).toBe(150);
    });

    // === EXTREME EDGE CASES ===

    it('fromData handles id 0', () => {
        const account = Account.fromData({
            id: 0,
            balance: 500,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.id).toBe(0);
        expect(account.balance.amount).toBe(500);
    });

    it('fromData handles NaN balance', () => {
        const account = Account.fromData({
            id: 1,
            balance: NaN,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(Number.isNaN(account.balance.amount)).toBe(true);
    });

    it('fromData handles invalid date string', () => {
        const account = Account.fromData({
            id: 1,
            balance: 100,
            created_at: 'not-a-date',
        });

        // JS: new Date('not-a-date') creates Invalid Date
        expect(account.createdAt.toString()).toBe('Invalid Date');
        expect(Number.isNaN(account.createdAt.getTime())).toBe(true);
    });

    it('fromData handles negative id', () => {
        const account = Account.fromData({
            id: -1,
            balance: 100,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.id).toBe(-1);
    });

    it('fromData handles fractional balance', () => {
        const account = Account.fromData({
            id: 1,
            balance: 0.01,
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(account.balance.amount).toBeCloseTo(0.01, 5);
        expect(account.balance.isPositive()).toBe(true);
    });
});
