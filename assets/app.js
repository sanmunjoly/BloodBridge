document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-modal-open]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.modalOpen)?.classList.add('open')));
  document.querySelectorAll('[data-modal-close]').forEach(button => button.addEventListener('click', () => button.closest('.modal')?.classList.remove('open')));
  document.querySelectorAll('.modal').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
  document.querySelectorAll('[data-stock-group]').forEach(button => button.addEventListener('click', () => { document.getElementById('stock-group').value = button.dataset.stockGroup; document.getElementById('stock-units').value = button.dataset.stockUnits; document.getElementById('stock-modal').classList.add('open'); }));
  document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => document.querySelector('.sidebar').classList.toggle('show'));
});