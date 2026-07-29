import { Account } from '../../domain/Account';
import { Activity } from '../../domain/Activity';
import { GetAccounts } from '../../application/use-cases/GetAccounts';
import { GetAccountDetail } from '../../application/use-cases/GetAccountDetail';
import { SendMoney } from '../../application/use-cases/SendMoney';
import { ApiAccountRepository } from '../api/ApiAccountRepository';

type NotificationType = 'success' | 'error' | 'info';

interface AppState {
  accounts: Account[];
  selectedAccountId: number | null;
  activities: Activity[];
  loading: boolean;
  notification: { message: string; type: NotificationType } | null;
}

export class Dashboard {
  private state: AppState = {
    accounts: [],
    selectedAccountId: null,
    activities: [],
    loading: true,
    notification: null,
  };

  private readonly getAccounts: GetAccounts;
  private readonly getAccountDetail: GetAccountDetail;
  private readonly sendMoney: SendMoney;
  private readonly repo: ApiAccountRepository;
  private readonly root: HTMLElement;

  constructor(root: HTMLElement) {
    this.root = root;
    this.repo = new ApiAccountRepository();
    this.getAccounts = new GetAccounts(this.repo);
    this.getAccountDetail = new GetAccountDetail(this.repo);
    this.sendMoney = new SendMoney(this.repo);
    this.init();
  }

  private async init(): Promise<void> {
    this.render();
    await this.loadAccounts();
  }

  private showNotification(message: string, type: NotificationType, duration = 4000): void {
    this.state = { ...this.state, notification: { message, type } };
    this.renderNotification();
    if (duration > 0) {
      setTimeout(() => {
        this.state = { ...this.state, notification: null };
        this.renderNotification();
      }, duration);
    }
  }

  private async loadAccounts(): Promise<void> {
    try {
      this.state = { ...this.state, loading: true };
      this.renderLoading();
      const accounts = await this.getAccounts.execute();
      this.state = { ...this.state, accounts, loading: false };
      if (accounts.length > 0) {
        this.state = { ...this.state, selectedAccountId: accounts[0].id };
        await this.loadActivities(accounts[0].id);
      }
      this.renderContent();
    } catch (err) {
      this.state = { ...this.state, loading: false };
      this.showNotification(
        `Failed to load accounts: ${err instanceof Error ? err.message : 'Unknown error'}`,
        'error',
      );
      this.renderContent();
    }
  }

  private async loadActivities(accountId: number): Promise<void> {
    try {
      const detail = await this.getAccountDetail.execute(accountId);
      this.state = { ...this.state, activities: detail.activities, selectedAccountId: accountId };
      this.renderActivities();
      this.renderAccountCards();
    } catch (err) {
      this.showNotification(
        `Failed to load activities: ${err instanceof Error ? err.message : 'Unknown error'}`,
        'error',
      );
    }
  }

  private async handleSendMoney(sourceId: number, targetId: number, amount: number): Promise<void> {
    try {
      const success = await this.sendMoney.execute(sourceId, targetId, amount);
      if (success) {
        this.showNotification(
          `Successfully sent $${amount} from Account #${sourceId} to Account #${targetId}!`,
          'success',
          5000,
        );
        await this.loadAccounts();
      } else {
        this.showNotification(
          `Transfer failed: insufficient funds in Account #${sourceId}.`,
          'error',
        );
      }
    } catch (err) {
      this.showNotification(
        `Transfer error: ${err instanceof Error ? err.message : 'Unknown error'}`,
        'error',
      );
    }
  }

  private async handleSeedAccounts(): Promise<void> {
    try {
      this.state = { ...this.state, loading: true };
      this.renderLoading();
      // Create two accounts with seed data
      const account1 = await this.repo.createAccount(undefined, true);
      const account2 = await this.repo.createAccount(undefined, true);
      this.showNotification(
        `Created Account #${account1.id} and Account #${account2.id}!`,
        'success',
      );
      await this.loadAccounts();
    } catch (err) {
      this.state = { ...this.state, loading: false };
      this.showNotification(
        `Failed to seed accounts: ${err instanceof Error ? err.message : 'Unknown error'}`,
        'error',
      );
      this.renderContent();
    }
  }

  // --- Rendering ---

  private render(): void {
    this.root.innerHTML = `
      <div class="app-shell">
        <header class="app-header">
          <div class="header-content">
            <div class="header-left">
              <div class="app-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 6v12M8 10l4-4 4 4"/>
                </svg>
                <h1>HexaBank</h1>
              </div>
              <p class="app-subtitle">Clean Architecture Demo</p>
            </div>
            <button class="btn btn-secondary seed-btn" id="seed-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
              </svg>
              Seed Demo Data
            </button>
          </div>
        </header>

        <div id="notification-container"></div>

        <main class="app-main">
          <div id="loading-container" class="loading-container"></div>
          <div id="content-container"></div>
        </main>

        <footer class="app-footer">
          <p>Built with Laravel 13 + PHP 8.5 · Hexagonal Architecture</p>
        </footer>
      </div>
    `;

    document.getElementById('seed-btn')?.addEventListener('click', () => this.handleSeedAccounts());
  }

  private renderLoading(): void {
    const container = document.getElementById('loading-container');
    if (container) {
      container.innerHTML = `
        <div class="loading-spinner">
          <div class="spinner"></div>
          <p>Loading accounts...</p>
        </div>
      `;
    }
  }

  private renderNotification(): void {
    const container = document.getElementById('notification-container');
    if (!container) return;

    if (!this.state.notification) {
      container.innerHTML = '';
      return;
    }

    const { message, type } = this.state.notification;
    container.innerHTML = `
      <div class="notification notification-${type}">
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
      </div>
    `;
  }

  private renderContent(): void {
    const container = document.getElementById('content-container');
    if (!container) return;

    const loadingContainer = document.getElementById('loading-container');
    if (loadingContainer) loadingContainer.innerHTML = '';

    if (this.state.accounts.length === 0 && !this.state.loading) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v12M8 10l4-4 4 4"/>
            </svg>
          </div>
          <h2>No Accounts Yet</h2>
          <p>Click "Seed Demo Data" to create sample accounts with transaction history.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div class="dashboard-grid">
        <section class="accounts-section">
          <div class="section-header">
            <h2>Accounts</h2>
            <span class="badge">${this.state.accounts.length}</span>
          </div>
          <div id="account-cards" class="account-cards"></div>
        </section>

        <section class="transfer-section">
          <div class="section-header">
            <h2>Send Money</h2>
          </div>
          <div id="send-money-form"></div>
        </section>

        <section class="activity-section">
          <div class="section-header">
            <h2>Recent Transactions</h2>
          </div>
          <div id="activity-list"></div>
        </section>
      </div>
    `;

    this.renderAccountCards();
    this.renderSendForm();
    this.renderActivities();
  }

  private renderAccountCards(): void {
    const container = document.getElementById('account-cards');
    if (!container) return;

    container.innerHTML = this.state.accounts
      .map(
        (account) => `
        <div class="account-card ${this.state.selectedAccountId === account.id ? 'selected' : ''}"
             data-account-id="${account.id}">
          <div class="account-card-header">
            <div class="account-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
            </div>
            <span class="account-number">#${account.id}</span>
          </div>
          <div class="account-balance">
            <span class="balance-label">Balance</span>
            <span class="balance-amount">$${account.balance.amount.toLocaleString()}</span>
          </div>
          <div class="account-created">Created ${account.createdAt.toLocaleDateString()}</div>
        </div>
      `,
      )
      .join('');

    container.querySelectorAll('.account-card').forEach((card) => {
      card.addEventListener('click', () => {
        const id = Number(card.getAttribute('data-account-id'));
        this.loadActivities(id);
      });
    });
  }

  private renderSendForm(): void {
    const container = document.getElementById('send-money-form');
    if (!container || this.state.accounts.length < 2) {
      if (container) {
        container.innerHTML = `
          <div class="form-disabled">
            <p>Need at least 2 accounts to transfer.</p>
          </div>
        `;
      }
      return;
    }

    const options = this.state.accounts
      .map(
        (a) =>
          `<option value="${a.id}">Account #${a.id} ($${a.balance.amount.toLocaleString()})</option>`,
      )
      .join('');

    container.innerHTML = `
      <form id="transfer-form" class="send-form">
        <div class="form-group">
          <label for="from-account">From</label>
          <div class="select-wrapper">
            <select id="from-account" required>
              <option value="">Select source account</option>
              ${options}
            </select>
            <svg class="select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </div>
        </div>

        <div class="form-group">
          <label for="to-account">To</label>
          <div class="select-wrapper">
            <select id="to-account" required>
              <option value="">Select target account</option>
              ${options}
            </select>
            <svg class="select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </div>
        </div>

        <div class="form-group">
          <label for="amount">Amount</label>
          <div class="input-wrapper">
            <span class="input-prefix">$</span>
            <input type="number" id="amount" min="1" max="1000000" placeholder="Enter amount" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <polyline points="19 12 12 19 5 12"/>
          </svg>
          Send Money
        </button>
      </form>
    `;

    document.getElementById('transfer-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target as HTMLFormElement;
      const fromId = Number((document.getElementById('from-account') as HTMLSelectElement).value);
      const toId = Number((document.getElementById('to-account') as HTMLSelectElement).value);
      const amount = Number((document.getElementById('amount') as HTMLInputElement).value);

      // Disable form during submission
      const submitBtn = form.querySelector('button[type="submit"]') as HTMLButtonElement;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="btn-spinner"></span> Processing...';

      await this.handleSendMoney(fromId, toId, amount);

      // Re-enable form
      submitBtn.disabled = false;
      submitBtn.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <polyline points="19 12 12 19 5 12"/>
        </svg>
        Send Money
      `;
      form.reset();
    });

    // Validate that from !== to
    const fromSelect = document.getElementById('from-account') as HTMLSelectElement;
    const toSelect = document.getElementById('to-account') as HTMLSelectElement;
    const validateAccounts = () => {
      if (fromSelect.value && toSelect.value && fromSelect.value === toSelect.value) {
        toSelect.setCustomValidity('Cannot send to the same account');
      } else {
        toSelect.setCustomValidity('');
      }
    };
    fromSelect.addEventListener('change', validateAccounts);
    toSelect.addEventListener('change', validateAccounts);
  }

  private renderActivities(): void {
    const container = document.getElementById('activity-list');
    if (!container) return;

    if (this.state.activities.length === 0) {
      container.innerHTML = `
        <div class="empty-activities">
          <p>No transactions yet for this account.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div class="activity-table-wrapper">
        <table class="activity-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Amount</th>
              <th>From</th>
              <th>To</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            ${this.state.activities
              .map(
                (activity) => `
              <tr class="activity-row ${activity.type}">
                <td>
                  <span class="activity-type-badge ${activity.type}">
                    ${activity.type === 'incoming' ? '↓' : '↑'}
                    ${activity.type === 'incoming' ? 'Deposit' : 'Withdrawal'}
                  </span>
                </td>
                <td class="amount ${activity.type}">
                  ${activity.type === 'incoming' ? '+' : '-'}$${activity.amount.amount.toLocaleString()}
                </td>
                <td>#${activity.sourceAccountId}</td>
                <td>#${activity.targetAccountId}</td>
                <td class="date">${activity.createdAt.toLocaleString()}</td>
              </tr>
            `,
              )
              .join('')}
          </tbody>
        </table>
      </div>
    `;
  }
}
