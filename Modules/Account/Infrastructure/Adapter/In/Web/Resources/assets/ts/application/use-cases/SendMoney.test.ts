import { describe, it, expect, vi } from 'vitest';
import { SendMoney } from './SendMoney';
import { AccountRepository } from '../ports/AccountRepository';

function createMockRepo(): AccountRepository {
    return {
        listAccounts: vi.fn(),
        getAccount: vi.fn(),
        sendMoney: vi.fn(),
        createAccount: vi.fn(),
    };
}

describe('SendMoney Use Case', () => {
    it('throws when source and target are the same', async () => {
        const useCase = new SendMoney(createMockRepo());

        await expect(useCase.execute(1, 1, 100)).rejects.toThrow(
            'Cannot send money to the same account',
        );
    });

    it('throws when amount is zero', async () => {
        const useCase = new SendMoney(createMockRepo());

        await expect(useCase.execute(1, 2, 0)).rejects.toThrow('Amount must be positive');
    });

    it('throws when amount is negative', async () => {
        const useCase = new SendMoney(createMockRepo());

        await expect(useCase.execute(1, 2, -100)).rejects.toThrow('Amount must be positive');
    });

    it('calls repository.sendMoney with correct args', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(1, 2, 500);

        expect(result).toBe(true);
        expect(mockRepo.sendMoney).toHaveBeenCalledWith(1, 2, 500);
    });

    it('returns false when transfer fails', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(false);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(1, 2, 500);

        expect(result).toBe(false);
    });

    // === EDGE CASES ===

    it('propagates repository error', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockRejectedValue(new Error('Database connection failed'));
        const useCase = new SendMoney(mockRepo);

        await expect(useCase.execute(1, 2, 100)).rejects.toThrow('Database connection failed');
    });

    it('handles floating point amount', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(1, 2, 99.99);

        expect(result).toBe(true);
        expect(mockRepo.sendMoney).toHaveBeenCalledWith(1, 2, 99.99);
    });

    it('handles very large amount', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(1, 2, 1_000_000_000);

        expect(result).toBe(true);
        expect(mockRepo.sendMoney).toHaveBeenCalledWith(1, 2, 1_000_000_000);
    });

    it('does not throw for negative amount in repository (backward compat)', async () => {
        // The use case validates amount > 0 before calling the repo,
        // but a future version might allow negative ref: backend allows it
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        // Amount must be positive for the use case
        await expect(useCase.execute(1, 2, -50)).rejects.toThrow('Amount must be positive');
        // Ensure repository was never called
        expect(mockRepo.sendMoney).not.toHaveBeenCalled();
    });

    it('does not call repository when validation fails (early exit)', async () => {
        const mockRepo = createMockRepo();
        const useCase = new SendMoney(mockRepo);

        await expect(useCase.execute(5, 5, 100)).rejects.toThrow('Cannot send money to the same account');
        expect(mockRepo.sendMoney).not.toHaveBeenCalled();

        await expect(useCase.execute(1, 2, 0)).rejects.toThrow('Amount must be positive');
        expect(mockRepo.sendMoney).not.toHaveBeenCalled();
    });

    it('handles account id zero as source', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(0, 1, 100);

        expect(result).toBe(true);
        expect(mockRepo.sendMoney).toHaveBeenCalledWith(0, 1, 100);
    });

    it('handles account id zero as target', async () => {
        const mockRepo = createMockRepo();
        mockRepo.sendMoney = vi.fn().mockResolvedValue(true);
        const useCase = new SendMoney(mockRepo);

        const result = await useCase.execute(1, 0, 100);

        expect(result).toBe(true);
        expect(mockRepo.sendMoney).toHaveBeenCalledWith(1, 0, 100);
    });

    it('cannot send to self with zero amount', async () => {
        const useCase = new SendMoney(createMockRepo());

        await expect(useCase.execute(1, 1, 0)).rejects.toThrow('Cannot send money to the same account');
    });
});
