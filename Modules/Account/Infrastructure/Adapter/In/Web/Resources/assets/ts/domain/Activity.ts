import { Money } from './Money';

export type ActivityType = 'incoming' | 'outgoing';

export interface ActivityData {
  id: number;
  amount: number;
  source_account_id: number;
  target_account_id: number;
  type: ActivityType;
  created_at: string;
}

export class Activity {
  constructor(
    public readonly id: number,
    public readonly amount: Money,
    public readonly sourceAccountId: number,
    public readonly targetAccountId: number,
    public readonly type: ActivityType,
    public readonly createdAt: Date,
  ) {}

  static fromData(data: ActivityData): Activity {
    return new Activity(
      data.id,
      Money.of(data.amount),
      data.source_account_id,
      data.target_account_id,
      data.type,
      new Date(data.created_at),
    );
  }
}
