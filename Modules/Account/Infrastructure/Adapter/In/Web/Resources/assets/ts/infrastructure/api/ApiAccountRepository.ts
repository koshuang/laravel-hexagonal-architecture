import axios from 'axios';
import { Account } from '../../domain/Account';
import { Activity } from '../../domain/Activity';
import { AccountRepository } from '../../application/ports/AccountRepository';

export interface AccountDetailData extends AccountData {
  activities: ActivityData[];
}

interface AccountData {
  id: number;
  balance: number;
  created_at: string;
}

interface ActivityData {
  id: number;
  amount: number;
  source_account_id: number;
  target_account_id: number;
  type: 'incoming' | 'outgoing';
  created_at: string;
}

export class ApiAccountRepository implements AccountRepository {
  private readonly baseUrl = '/api/accounts';

  constructor() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
  }

  async listAccounts(): Promise<Account[]> {
    const response = await axios.get<{ data: AccountData[] }>(this.baseUrl);
    return response.data.data.map((item) => Account.fromData(item));
  }

  async getAccount(id: number): Promise<{ account: Account; activities: Activity[] }> {
    const response = await axios.get<{ data: AccountDetailData }>(`${this.baseUrl}/${id}`);
    const detail = response.data.data;
    return {
      account: Account.fromData(detail),
      activities: detail.activities.map((a) => Activity.fromData(a)),
    };
  }

  async sendMoney(sourceId: number, targetId: number, amount: number): Promise<boolean> {
    const response = await axios.post<{ success: boolean }>(
      `${this.baseUrl}/send/${sourceId}/${targetId}/${amount}`,
    );
    return response.data.success;
  }

  async createAccount(id?: number, seedActivities = true): Promise<Account> {
    const response = await axios.post<{ data: { id: number } }>(this.baseUrl, {
      id,
      seed_activities: seedActivities,
    });
    const { account } = await this.getAccount(response.data.data.id);
    return account;
  }
}
