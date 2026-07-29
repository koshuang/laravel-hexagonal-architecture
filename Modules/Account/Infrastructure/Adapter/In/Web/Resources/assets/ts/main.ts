import { Dashboard } from './infrastructure/ui/Dashboard';

document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('app');
  if (!root) {
    console.error('App root element not found');
    return;
  }
  new Dashboard(root);
});
