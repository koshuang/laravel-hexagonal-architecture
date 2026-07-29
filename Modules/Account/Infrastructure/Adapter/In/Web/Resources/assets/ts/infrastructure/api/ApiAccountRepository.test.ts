import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import axios from 'axios';
import { ApiAccountRepository } from './ApiAccountRepository';

// Mock the entire axios module
vi.mock('axios');

beforeEach(() => {
    vi.clearAllMocks();

    // Mock CSRF meta tag
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = 'test-csrf-token';
    document.head.appendChild(meta);
});

afterEach(() => {
    document.head.querySelector('meta[name="csrf-token"]')?.remove();
    // Clean up axios defaults set by constructor
    delete axios.defaults.headers.common['X-CSRF-TOKEN'];
});

describe('ApiAccountRepository', () => {
    const createRepo = () => new ApiAccountRepository();

    describe('constructor', () => {
        it('sets CSRF token from meta tag', () => {
            createRepo();
            expect(axios.defaults.headers.common['X-CSRF-TOKEN']).toBe('test-csrf-token');
        });

        it('handles missing CSRF meta tag gracefully', () => {
            document.head.querySelector('meta[name="csrf-token"]')?.remove();
            createRepo();
            // Should not throw, just skip setting the header
            expect(axios.defaults.headers.common['X-CSRF-TOKEN']).toBeUndefined();
        });
    });

    describe('listAccounts', () => {
        it('returns mapped accounts on success', async () => {
            const mockAccounts = {
                data: {
                    data: [
                        { id: 1, balance: 500, created_at: '2026-07-28T12:00:00.000Z' },
                        { id: 2, balance: 1000, created_at: '2026-07-28T13:00:00.000Z' },
                    ],
                },
            };
            vi.mocked(axios.get).mockResolvedValue(mockAccounts);

            const accounts = await createRepo().listAccounts();

            expect(accounts).toHaveLength(2);
            expect(accounts[0].id).toBe(1);
            expect(accounts[0].balance.amount).toBe(500);
            expect(accounts[1].id).toBe(2);
            expect(accounts[1].balance.amount).toBe(1000);
            expect(axios.get).toHaveBeenCalledWith('/api/accounts');
        });

        it('returns empty array when no accounts exist', async () => {
            vi.mocked(axios.get).mockResolvedValue({ data: { data: [] } });

            const accounts = await createRepo().listAccounts();

            expect(accounts).toHaveLength(0);
        });

        it('throws on network error', async () => {
            vi.mocked(axios.get).mockRejectedValue(new Error('Network Error'));

            await expect(createRepo().listAccounts()).rejects.toThrow('Network Error');
        });

        it('throws on 500 server error', async () => {
            const serverError = {
                response: { status: 500, data: { message: 'Server Error' } },
            };
            vi.mocked(axios.get).mockRejectedValue(serverError);

            await expect(createRepo().listAccounts()).rejects.toEqual(serverError);
        });
    });

    describe('getAccount', () => {
        it('returns account detail with mapped activities', async () => {
            const mockDetail = {
                data: {
                    data: {
                        id: 1,
                        balance: 500,
                        created_at: '2026-07-28T12:00:00.000Z',
                        activities: [
                            {
                                id: 10,
                                amount: 200,
                                source_account_id: 2,
                                target_account_id: 1,
                                type: 'incoming',
                                created_at: '2026-07-28T13:00:00.000Z',
                            },
                            {
                                id: 11,
                                amount: 100,
                                source_account_id: 1,
                                target_account_id: 2,
                                type: 'outgoing',
                                created_at: '2026-07-28T14:00:00.000Z',
                            },
                        ],
                    },
                },
            };
            vi.mocked(axios.get).mockResolvedValue(mockDetail);

            const result = await createRepo().getAccount(1);

            expect(result.account.id).toBe(1);
            expect(result.account.balance.amount).toBe(500);
            expect(result.activities).toHaveLength(2);
            expect(result.activities[0].type).toBe('incoming');
            expect(result.activities[1].type).toBe('outgoing');
            expect(axios.get).toHaveBeenCalledWith('/api/accounts/1');
        });

        it('throws 404 when account not found', async () => {
            const notFoundError = {
                response: { status: 404, data: { message: 'Not Found' } },
            };
            vi.mocked(axios.get).mockRejectedValue(notFoundError);

            await expect(createRepo().getAccount(999)).rejects.toEqual(notFoundError);
        });

        it('handles account with no activities', async () => {
            const mockDetail = {
                data: {
                    data: {
                        id: 1,
                        balance: 0,
                        created_at: '2026-07-28T12:00:00.000Z',
                        activities: [],
                    },
                },
            };
            vi.mocked(axios.get).mockResolvedValue(mockDetail);

            const result = await createRepo().getAccount(1);

            expect(result.activities).toHaveLength(0);
            expect(result.account.balance.amount).toBe(0);
        });

        it('handles account with single activity', async () => {
            const mockDetail = {
                data: {
                    data: {
                        id: 1,
                        balance: 100,
                        created_at: '2026-07-28T12:00:00.000Z',
                        activities: [
                            {
                                id: 10,
                                amount: 100,
                                source_account_id: 2,
                                target_account_id: 1,
                                type: 'incoming',
                                created_at: '2026-07-28T13:00:00.000Z',
                            },
                        ],
                    },
                },
            };
            vi.mocked(axios.get).mockResolvedValue(mockDetail);

            const result = await createRepo().getAccount(1);

            expect(result.activities).toHaveLength(1);
            expect(result.activities[0].amount.amount).toBe(100);
        });
    });

    describe('sendMoney', () => {
        it('returns true on successful transfer', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { success: true } });

            const result = await createRepo().sendMoney(1, 2, 500);

            expect(result).toBe(true);
            expect(axios.post).toHaveBeenCalledWith('/api/accounts/send/1/2/500');
        });

        it('returns false when transfer fails', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { success: false } });

            const result = await createRepo().sendMoney(1, 2, 500);

            expect(result).toBe(false);
        });

        it('throws on network error during transfer', async () => {
            vi.mocked(axios.post).mockRejectedValue(new Error('Network Error'));

            await expect(createRepo().sendMoney(1, 2, 100)).rejects.toThrow('Network Error');
        });

        it('handles floating point amounts', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { success: true } });

            const result = await createRepo().sendMoney(1, 2, 99.99);

            expect(result).toBe(true);
            expect(axios.post).toHaveBeenCalledWith('/api/accounts/send/1/2/99.99');
        });
    });

    describe('createAccount', () => {
        it('creates account and fetches detail', async () => {
            // First call: POST returns new account id
            vi.mocked(axios.post).mockResolvedValue({ data: { data: { id: 42 } } });
            // Second call: GET returns full detail
            vi.mocked(axios.get).mockResolvedValue({
                data: {
                    data: {
                        id: 42,
                        balance: 1000,
                        created_at: '2026-07-28T15:00:00.000Z',
                        activities: [
                            {
                                id: 1,
                                amount: 1000,
                                source_account_id: 0,
                                target_account_id: 42,
                                type: 'incoming',
                                created_at: '2026-07-28T15:00:00.000Z',
                            },
                        ],
                    },
                },
            });

            const account = await createRepo().createAccount();

            expect(account.id).toBe(42);
            expect(account.balance.amount).toBe(1000);
            // Should call POST then GET
            expect(axios.post).toHaveBeenCalledWith('/api/accounts', {
                seed_activities: true,
            });
            expect(axios.get).toHaveBeenCalledWith('/api/accounts/42');
        });

        it('creates account without seed activities', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { data: { id: 43 } } });
            vi.mocked(axios.get).mockResolvedValue({
                data: {
                    data: {
                        id: 43,
                        balance: 0,
                        created_at: '2026-07-28T15:00:00.000Z',
                        activities: [],
                    },
                },
            });

            const account = await createRepo().createAccount(undefined, false);

            expect(account.id).toBe(43);
            expect(axios.post).toHaveBeenCalledWith('/api/accounts', {
                seed_activities: false,
            });
        });

        it('creates account with specific id', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { data: { id: 99 } } });
            vi.mocked(axios.get).mockResolvedValue({
                data: {
                    data: {
                        id: 99,
                        balance: 0,
                        created_at: '2026-07-28T15:00:00.000Z',
                        activities: [],
                    },
                },
            });

            const account = await createRepo().createAccount(99);

            expect(account.id).toBe(99);
            // Repository sends { id: 99, seed_activities: true } in POST body
            expect(axios.post).toHaveBeenCalledWith('/api/accounts', {
                id: 99,
                seed_activities: true,
            });
        });

        it('throws when POST succeeds but GET fails', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { data: { id: 42 } } });
            vi.mocked(axios.get).mockRejectedValue(new Error('GET failed'));

            await expect(createRepo().createAccount()).rejects.toThrow('GET failed');
        });

        it('throws on network error during create', async () => {
            vi.mocked(axios.post).mockRejectedValue(new Error('Network Error'));

            await expect(createRepo().createAccount()).rejects.toThrow('Network Error');
        });
    });

    describe('edge cases across methods', () => {
        it('handles accounts with negative balance in list', async () => {
            vi.mocked(axios.get).mockResolvedValue({
                data: {
                    data: [
                        { id: 1, balance: -200, created_at: '2026-07-28T12:00:00.000Z' },
                    ],
                },
            });

            const accounts = await createRepo().listAccounts();

            expect(accounts[0].balance.amount).toBe(-200);
            expect(accounts[0].balance.isNegative()).toBe(true);
        });

        it('handles very large balance values', async () => {
            vi.mocked(axios.get).mockResolvedValue({
                data: {
                    data: [
                        { id: 1, balance: 9_999_999_999_999, created_at: '2026-07-28T12:00:00.000Z' },
                    ],
                },
            });

            const accounts = await createRepo().listAccounts();

            expect(accounts[0].balance.amount).toBe(9_999_999_999_999);
        });

        it('multiple repository instances do not conflict', async () => {
            vi.mocked(axios.get).mockResolvedValue({ data: { data: [] } });

            const repo1 = createRepo();
            const repo2 = createRepo();

            await repo1.listAccounts();
            await repo2.listAccounts();

            // Each instance should make its own calls
            expect(axios.get).toHaveBeenCalledTimes(2);
        });

        it('handles sendMoney with account id zero', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { success: true } });

            const result = await createRepo().sendMoney(0, 1, 100);

            expect(result).toBe(true);
            expect(axios.post).toHaveBeenCalledWith('/api/accounts/send/0/1/100');
        });

        it('handles sendMoney with very large amount', async () => {
            vi.mocked(axios.post).mockResolvedValue({ data: { success: true } });

            const result = await createRepo().sendMoney(1, 2, 9_999_999_999);

            expect(result).toBe(true);
            expect(axios.post).toHaveBeenCalledWith('/api/accounts/send/1/2/9999999999');
        });
    });
});
