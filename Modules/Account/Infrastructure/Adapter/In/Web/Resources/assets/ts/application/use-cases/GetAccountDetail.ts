import { Account } from '../../domain/Account';
import { Activity } from '../../domain/Activity';
import { AccountRepository } from '../ports/AccountRepository';

export interface AccountDetail {
  account: Account;
  activities: Activity[];
}

export class GetAccountDetail {
  constructor(private readonly repository: AccountRepository) {}

  async execute(id: number): Promise<AccountDetail> {
    return this.repository.getAccount(id);
  }
}
