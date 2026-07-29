import { AccountRepository } from '../ports/AccountRepository';

export class SendMoney {
  constructor(private readonly repository: AccountRepository) {}

  async execute(sourceId: number, targetId: number, amount: number): Promise<boolean> {
    if (sourceId === targetId) {
      throw new Error('Cannot send money to the same account');
    }
    if (amount <= 0) {
      throw new Error('Amount must be positive');
    }
    return this.repository.sendMoney(sourceId, targetId, amount);
  }
}
