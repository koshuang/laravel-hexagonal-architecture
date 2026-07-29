import { describe, it, expect, vi } from 'vitest';
import { GetAccounts } from './GetAccounts';
import { AccountRepository } from '../ports/AccountRepository';
import { Account } from '../../domain/Account';
import { Money } from '../../domain/Money';

function createMockRepo(): AccountRepository {
    return {
        listAccounts: vi.fn(),
        getAccount: vi.fn(),
        sendMoney: vi.fn(),
        createAccount: vi.fn(),
    };
}

describe('GetAccounts Use Case', () => {
    it('returns accounts from repository', async () => {
        const expectedAccounts = [
            new Account(1, Money.of(500), new Date()),
            new Account(2, Money.of(1000), new Date()),
        ];

        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockResolvedValue(expectedAccounts);
        const useCase = new GetAccounts(mockRepo);

        const accounts = await useCase.execute();

        expect(accounts).toHaveLength(2);
        expect(accounts[0].id).toBe(1);
        expect(accounts[0].balance.amount).toBe(500);
        expect(mockRepo.listAccounts).toHaveBeenCalledOnce();
    });

    it('returns empty array when no accounts', async () => {
        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockResolvedValue([]);
        const useCase = new GetAccounts(mockRepo);

        const accounts = await useCase.execute();

        expect(accounts).toHaveLength(0);
    });

    // === EDGE CASES ===

    it('propagates repository error', async () => {
        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockRejectedValue(new Error('Database unavailable'));
        const useCase = new GetAccounts(mockRepo);

        await expect(useCase.execute()).rejects.toThrow('Database unavailable');
    });

    it('handles accounts with negative balance', async () => {
        const accountsWithNegativeBalance = [
            new Account(1, Money.of(-100), new Date()),
            new Account(2, Money.of(200), new Date()),
        ];

        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockResolvedValue(accountsWithNegativeBalance);
        const useCase = new GetAccounts(mockRepo);

        const accounts = await useCase.execute();

        expect(accounts[0].balance.isNegative()).toBe(true);
        expect(accounts[0].balance.amount).toBe(-100);
        expect(accounts[1].balance.isPositive()).toBe(true);
    });

    it('handles accounts with zero balance', async () => {
        const zeroBalanceAccounts = [
            new Account(1, Money.ZERO(), new Date()),
        ];

        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockResolvedValue(zeroBalanceAccounts);
        const useCase = new GetAccounts(mockRepo);

        const accounts = await useCase.execute();

        expect(accounts).toHaveLength(1);
        expect(accounts[0].balance.amount).toBe(0);
        expect(accounts[0].balance.isPositiveOrZero()).toBe(true);
    });

    it('handles many accounts', async () => {
        const manyAccounts = Array.from({ length: 100 }, (_, i) =>
            new Account(i + 1, Money.of(i * 100), new Date()),
        );

        const mockRepo = createMockRepo();
        mockRepo.listAccounts = vi.fn().mockResolvedValue(manyAccounts);
        const useCase = new GetAccounts(mockRepo);

        const accounts = await useCase.execute();

        expect(accounts).toHaveLength(100);
        expect(accounts[0].id).toBe(1);
        expect(accounts[99].id).toBe(100);
        expect(accounts[99].balance.amount).toBe(9900);
    });
});
