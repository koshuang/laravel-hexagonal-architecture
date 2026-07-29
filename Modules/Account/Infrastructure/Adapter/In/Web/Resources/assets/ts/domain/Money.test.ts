import { describe, it, expect } from 'vitest';
import { Money } from './Money';

describe('Money Value Object', () => {
    it('creates money with given amount', () => {
        const money = Money.of(100);
        expect(money.amount).toBe(100);
    });

    it('creates zero money', () => {
        const zero = Money.ZERO();
        expect(zero.amount).toBe(0);
    });

    it('allows negative amount matching backend behavior', () => {
        const money = Money.of(-5);
        expect(money.amount).toBe(-5);
        expect(money.isNegative()).toBe(true);
    });

    it('checks if positive or zero', () => {
        expect(Money.of(5).isPositiveOrZero()).toBe(true);
        expect(Money.of(0).isPositiveOrZero()).toBe(true);
        expect(Money.of(-5).isPositiveOrZero()).toBe(false);
    });

    it('checks if positive', () => {
        expect(Money.of(5).isPositive()).toBe(true);
        expect(Money.of(0).isPositive()).toBe(false);
    });

    it('checks if negative', () => {
        expect(Money.of(-5).isNegative()).toBe(true);
        expect(Money.of(0).isNegative()).toBe(false);
    });

    it('adds two money amounts', () => {
        const a = Money.of(100);
        const b = Money.of(200);
        const result = a.plus(b);
        expect(result.amount).toBe(300);
    });

    it('subtracts money', () => {
        const a = Money.of(300);
        const b = Money.of(100);
        const result = a.minus(b);
        expect(result.amount).toBe(200);
    });

    it('compares amounts', () => {
        const bigger = Money.of(200);
        const smaller = Money.of(100);
        expect(bigger.isGreaterThan(smaller)).toBe(true);
        expect(smaller.isGreaterThan(bigger)).toBe(false);
        expect(bigger.isGreaterThanOrEqualTo(smaller)).toBe(true);
        expect(bigger.isGreaterThanOrEqualTo(Money.of(200))).toBe(true);
    });

    it('preserves immutability on operations', () => {
        const original = Money.of(100);
        original.plus(Money.of(50));
        expect(original.amount).toBe(100);
    });

    // === EDGE CASES ===

    it('handles floating point amounts', () => {
        expect(Money.of(0.1).amount).toBe(0.1);
        expect(Money.of(99.99).amount).toBe(99.99);
    });

    it('handles floating point arithmetic', () => {
        const a = Money.of(0.1);
        const b = Money.of(0.2);
        const result = a.plus(b);
        // JavaScript floating point: 0.1 + 0.2 = 0.30000000000000004
        expect(result.amount).toBeCloseTo(0.3, 10);
    });

    it('adds with negative amount (net subtraction)', () => {
        const a = Money.of(100);
        const b = Money.of(-30);
        const result = a.plus(b);
        expect(result.amount).toBe(70);
    });

    it('subtracts negative amount (net addition)', () => {
        const a = Money.of(100);
        const b = Money.of(-30);
        const result = a.minus(b);
        expect(result.amount).toBe(130);
    });

    it('subtracts more than current balance (result goes negative)', () => {
        const a = Money.of(50);
        const b = Money.of(200);
        const result = a.minus(b);
        expect(result.amount).toBe(-150);
        expect(result.isNegative()).toBe(true);
    });

    it('isGreaterThan with equal values returns false', () => {
        expect(Money.of(100).isGreaterThan(Money.of(100))).toBe(false);
    });

    it('isGreaterThanOrEqualTo with smaller value returns false', () => {
        expect(Money.of(50).isGreaterThanOrEqualTo(Money.of(100))).toBe(false);
    });

    it('chains multiple operations', () => {
        const result = Money.of(100)
            .plus(Money.of(50))
            .minus(Money.of(30))
            .plus(Money.of(80));
        expect(result.amount).toBe(200);
    });

    it('handles very large numbers', () => {
        const large = 1_000_000_000_000;
        const money = Money.of(large);
        expect(money.amount).toBe(large);
        expect(money.plus(Money.of(large)).amount).toBe(large * 2);
    });

    it('plus with ZERO returns same amount', () => {
        const money = Money.of(42);
        const result = money.plus(Money.ZERO());
        expect(result.amount).toBe(42);
    });

    it('minus with ZERO returns same amount', () => {
        const money = Money.of(42);
        const result = money.minus(Money.ZERO());
        expect(result.amount).toBe(42);
    });

    it('ZERO is always the same value', () => {
        expect(Money.ZERO().amount).toBe(0);
        expect(Money.ZERO().isPositive()).toBe(false);
        expect(Money.ZERO().isNegative()).toBe(false);
        expect(Money.ZERO().isPositiveOrZero()).toBe(true);
    });

    it('of() returns new instances each call', () => {
        const a = Money.of(100);
        const b = Money.of(100);
        // Modifying one should not affect the other
        const c = a.plus(Money.of(1));
        expect(a.amount).toBe(100);
        expect(b.amount).toBe(100);
        expect(c.amount).toBe(101);
    });

    it('handles large negative amounts', () => {
        const money = Money.of(-999_999_999);
        expect(money.isNegative()).toBe(true);
        expect(money.isPositiveOrZero()).toBe(false);
    });

    it('isPositiveOrZero with different comparisons', () => {
        expect(Money.of(0).isPositiveOrZero()).toBe(true);
        expect(Money.of(0.01).isPositiveOrZero()).toBe(true);
        expect(Money.of(-0.01).isPositiveOrZero()).toBe(false);
    });

    // === EXTREME EDGE CASES ===

    it('handles NaN amounts', () => {
        const money = Money.of(NaN);
        expect(Number.isNaN(money.amount)).toBe(true);
        expect(money.isNegative()).toBe(false);
        expect(money.isPositive()).toBe(false);
        expect(money.isPositiveOrZero()).toBe(false);
    });

    it('handles Infinity amounts', () => {
        const money = Money.of(Infinity);
        expect(money.amount).toBe(Infinity);
        expect(money.isPositive()).toBe(true);
    });

    it('handles negative Infinity amounts', () => {
        const money = Money.of(-Infinity);
        expect(money.amount).toBe(-Infinity);
        expect(money.isNegative()).toBe(true);
    });

    it('handles negative zero (-0)', () => {
        const money = Money.of(-0);
        // Object.is(-0, 0) is false in JS
        expect(Object.is(money.amount, -0)).toBe(true);
        expect(money.isPositiveOrZero()).toBe(true);
        expect(money.isNegative()).toBe(false);
    });

    it('handles very small positive amounts', () => {
        const money = Money.of(0.001);
        expect(money.amount).toBe(0.001);
        expect(money.isPositive()).toBe(true);
        expect(money.isPositiveOrZero()).toBe(true);
    });

    it('NaN comparison with isGreaterThan', () => {
        // NaN comparisons are always false in JS
        const nanMoney = Money.of(NaN);
        expect(nanMoney.isGreaterThan(Money.of(0))).toBe(false);
        expect(nanMoney.isGreaterThanOrEqualTo(Money.of(0))).toBe(false);
        expect(Money.of(0).isGreaterThan(nanMoney)).toBe(false);
    });

    it('plus with NaN results in NaN', () => {
        const result = Money.of(100).plus(Money.of(NaN));
        expect(Number.isNaN(result.amount)).toBe(true);
    });

    it('ZERO is immutable - plus returns new instance', () => {
        const zero = Money.ZERO();
        const result = zero.plus(Money.of(50));
        expect(zero.amount).toBe(0);
        expect(result.amount).toBe(50);
    });

    it('ZERO minus anything goes negative', () => {
        const result = Money.ZERO().minus(Money.of(1));
        expect(result.amount).toBe(-1);
        expect(result.isNegative()).toBe(true);
    });

    it('chain starting from ZERO', () => {
        const result = Money.ZERO()
            .plus(Money.of(100))
            .minus(Money.of(30))
            .plus(Money.of(5));
        expect(result.amount).toBe(75);
    });

    it('handles maximum safe integer', () => {
        const max = Number.MAX_SAFE_INTEGER;
        const money = Money.of(max);
        expect(money.amount).toBe(max);
        const doubled = money.plus(Money.of(max));
        expect(doubled.amount).toBe(max * 2);
    });
});
