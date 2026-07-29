import { describe, it, expect, vi } from 'vitest';
import { GetAccountDetail } from './GetAccountDetail';
import { AccountRepository } from '../ports/AccountRepository';
import { Account } from '../../domain/Account';
import { Money } from '../../domain/Money';
import { Activity } from '../../domain/Activity';

function createMockRepo(): AccountRepository {
    return {
        listAccounts: vi.fn(),
        getAccount: vi.fn(),
        sendMoney: vi.fn(),
        createAccount: vi.fn(),
    };
}

describe('GetAccountDetail Use Case', () => {
    it('returns account with activities', async () => {
        const expectedDetail = {
            account: new Account(1, Money.of(500), new Date()),
            activities: [
                new Activity(1, Money.of(100), 1, 2, 'outgoing', new Date()),
                new Activity(2, Money.of(200), 2, 1, 'incoming', new Date()),
            ],
        };

        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockResolvedValue(expectedDetail);
        const useCase = new GetAccountDetail(mockRepo);

        const result = await useCase.execute(1);

        expect(result.account.id).toBe(1);
        expect(result.activities).toHaveLength(2);
        expect(result.activities[0].type).toBe('outgoing');
        expect(mockRepo.getAccount).toHaveBeenCalledWith(1);
    });

    it('throws when repository fails', async () => {
        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockRejectedValue(new Error('Account not found'));
        const useCase = new GetAccountDetail(mockRepo);

        await expect(useCase.execute(999)).rejects.toThrow('Account not found');
    });

    // === EDGE CASES ===

    it('handles account with no activities', async () => {
        const detailWithNoActivities = {
            account: new Account(1, Money.of(0), new Date()),
            activities: [] as Activity[],
        };

        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockResolvedValue(detailWithNoActivities);
        const useCase = new GetAccountDetail(mockRepo);

        const result = await useCase.execute(1);

        expect(result.account.id).toBe(1);
        expect(result.activities).toHaveLength(0);
    });

    it('handles account with one activity (edge case)', async () => {
        const detailWithOneActivity = {
            account: new Account(1, Money.of(100), new Date()),
            activities: [
                new Activity(1, Money.of(100), 2, 1, 'incoming', new Date()),
            ],
        };

        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockResolvedValue(detailWithOneActivity);
        const useCase = new GetAccountDetail(mockRepo);

        const result = await useCase.execute(1);

        expect(result.activities).toHaveLength(1);
        expect(result.activities[0].type).toBe('incoming');
        expect(result.activities[0].amount.amount).toBe(100);
    });

    it('handles account with negative balance', async () => {
        const detailWithNegativeBalance = {
            account: new Account(1, Money.of(-500), new Date()),
            activities: [
                new Activity(1, Money.of(500), 1, 2, 'outgoing', new Date()),
            ],
        };

        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockResolvedValue(detailWithNegativeBalance);
        const useCase = new GetAccountDetail(mockRepo);

        const result = await useCase.execute(1);

        expect(result.account.balance.amount).toBe(-500);
        expect(result.account.balance.isNegative()).toBe(true);
    });

    it('passes negative id through to repository', async () => {
        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockRejectedValue(new Error('Not found'));
        const useCase = new GetAccountDetail(mockRepo);

        await expect(useCase.execute(-1)).rejects.toThrow('Not found');
        expect(mockRepo.getAccount).toHaveBeenCalledWith(-1);
    });

    it('passes id 0 through to repository', async () => {
        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockRejectedValue(new Error('Not found'));
        const useCase = new GetAccountDetail(mockRepo);

        await expect(useCase.execute(0)).rejects.toThrow('Not found');
        expect(mockRepo.getAccount).toHaveBeenCalledWith(0);
    });

    it('handles many activities', async () => {
        const manyActivities = Array.from({ length: 50 }, (_, i) =>
            new Activity(i + 1, Money.of(100), i % 2 + 1, (i % 2) + 1, 'incoming', new Date()),
        );

        const detailWithManyActivities = {
            account: new Account(1, Money.of(5000), new Date()),
            activities: manyActivities,
        };

        const mockRepo = createMockRepo();
        mockRepo.getAccount = vi.fn().mockResolvedValue(detailWithManyActivities);
        const useCase = new GetAccountDetail(mockRepo);

        const result = await useCase.execute(1);

        expect(result.activities).toHaveLength(50);
    });
});
