import { Money } from './Money';

export interface AccountData {
  id: number;
  balance: number;
  created_at: string;
}

export class Account {
  constructor(
    public readonly id: number,
    public readonly balance: Money,
    public readonly createdAt: Date,
  ) {}

  static fromData(data: AccountData): Account {
    return new Account(
      data.id,
      Money.of(data.balance),
      new Date(data.created_at),
    );
  }
}
