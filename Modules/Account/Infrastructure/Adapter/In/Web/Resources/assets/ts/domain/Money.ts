export class Money {
  constructor(private readonly _amount: number) {}

  get amount(): number {
    return this._amount;
  }

  static ZERO(): Money {
    return new Money(0);
  }

  static of(amount: number): Money {
    return new Money(amount);
  }

  isPositiveOrZero(): boolean {
    return this._amount >= 0;
  }

  isPositive(): boolean {
    return this._amount > 0;
  }

  isNegative(): boolean {
    return this._amount < 0;
  }

  isGreaterThanOrEqualTo(money: Money): boolean {
    return this._amount >= money._amount;
  }

  isGreaterThan(money: Money): boolean {
    return this._amount > money._amount;
  }

  plus(money: Money): Money {
    return new Money(this._amount + money._amount);
  }

  minus(money: Money): Money {
    return new Money(this._amount - money._amount);
  }
}
