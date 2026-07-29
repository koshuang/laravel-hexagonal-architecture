import { Account } from '../../domain/Account';
import { Activity } from '../../domain/Activity';

export interface AccountRepository {
  listAccounts(): Promise<Account[]>;
  getAccount(id: number): Promise<{ account: Account; activities: Activity[] }>;
  sendMoney(sourceId: number, targetId: number, amount: number): Promise<boolean>;
  createAccount(id?: number, seedActivities?: boolean): Promise<Account>;
}
