import { describe, it, expect } from 'vitest';
import { Activity } from './Activity';
import { Money } from './Money';

describe('Activity Domain Entity', () => {
    it('creates activity with all properties', () => {
        const date = new Date('2026-07-28T12:00:00.000Z');
        const activity = new Activity(1, Money.of(500), 10, 20, 'outgoing', date);

        expect(activity.id).toBe(1);
        expect(activity.amount.amount).toBe(500);
        expect(activity.sourceAccountId).toBe(10);
        expect(activity.targetAccountId).toBe(20);
        expect(activity.type).toBe('outgoing');
        expect(activity.createdAt).toBe(date);
    });

    it('fromData maps incoming activity correctly', () => {
        const activity = Activity.fromData({
            id: 5,
            amount: 1000,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.id).toBe(5);
        expect(activity.amount.amount).toBe(1000);
        expect(activity.sourceAccountId).toBe(1);
        expect(activity.targetAccountId).toBe(2);
        expect(activity.type).toBe('incoming');
    });

    it('fromData maps outgoing activity correctly', () => {
        const activity = Activity.fromData({
            id: 6,
            amount: 200,
            source_account_id: 3,
            target_account_id: 4,
            type: 'outgoing',
            created_at: '2026-07-28T13:00:00.000Z',
        });

        expect(activity.type).toBe('outgoing');
        expect(activity.sourceAccountId).toBe(3);
        expect(activity.targetAccountId).toBe(4);
    });

    it('fromData handles zero amount', () => {
        const activity = Activity.fromData({
            id: 7,
            amount: 0,
            source_account_id: 1,
            target_account_id: 1,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.amount.amount).toBe(0);
        expect(activity.amount.amount).toBe(0);
        expect(activity.amount.isPositiveOrZero()).toBe(true);
    });

    it('fromData handles negative amount', () => {
        const activity = Activity.fromData({
            id: 8,
            amount: -50,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.amount.amount).toBe(-50);
        expect(activity.amount.isNegative()).toBe(true);
    });

    it('fromData handles same source and target (self-transfer)', () => {
        const activity = Activity.fromData({
            id: 9,
            amount: 100,
            source_account_id: 5,
            target_account_id: 5,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.sourceAccountId).toBe(5);
        expect(activity.targetAccountId).toBe(5);
    });

    it('fromData handles large amounts', () => {
        const activity = Activity.fromData({
            id: 10,
            amount: 9_999_999_999,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.amount.amount).toBe(9_999_999_999);
    });

    // === EXTREME EDGE CASES ===

    it('fromData handles id 0', () => {
        const activity = Activity.fromData({
            id: 0,
            amount: 100,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.id).toBe(0);
    });

    it('fromData handles invalid date string', () => {
        const activity = Activity.fromData({
            id: 1,
            amount: 100,
            source_account_id: 1,
            target_account_id: 2,
            type: 'outgoing',
            created_at: 'not-a-date',
        });

        expect(activity.createdAt.toString()).toBe('Invalid Date');
        expect(Number.isNaN(activity.createdAt.getTime())).toBe(true);
    });

    it('fromData handles negative id', () => {
        const activity = Activity.fromData({
            id: -5,
            amount: 100,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.id).toBe(-5);
    });

    it('fromData handles NaN amount', () => {
        const activity = Activity.fromData({
            id: 1,
            amount: NaN,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(Number.isNaN(activity.amount.amount)).toBe(true);
    });

    it('fromData handles fractional amount', () => {
        const activity = Activity.fromData({
            id: 1,
            amount: 0.25,
            source_account_id: 1,
            target_account_id: 2,
            type: 'incoming',
            created_at: '2026-07-28T12:00:00.000Z',
        });

        expect(activity.amount.amount).toBeCloseTo(0.25, 5);
    });
});
