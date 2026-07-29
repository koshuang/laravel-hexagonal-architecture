import { Account } from '../../domain/Account';
import { AccountRepository } from '../ports/AccountRepository';

export class GetAccounts {
  constructor(private readonly repository: AccountRepository) {}

  async execute(): Promise<Account[]> {
    return this.repository.listAccounts();
  }
}
