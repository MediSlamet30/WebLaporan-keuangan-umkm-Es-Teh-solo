// script.js
document.addEventListener('DOMContentLoaded', () => {
  const addBtn = document.getElementById('addBtn');
  const productSelect = document.getElementById('productSelect');
  const qtyInput = document.getElementById('qty');
  const tbody = document.querySelector('#cartTable tbody');
  const totalText = document.getElementById('totalText');
  const saveBtn = document.getElementById('saveBtn');
  const clearBtn = document.getElementById('clearBtn');

  let cart = []; // {id, name, price, qty}

  function numberFormat(n){
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function renderCart(){
    tbody.innerHTML = '';
    let total = 0;
    cart.forEach((it, idx) => {
      const tr = document.createElement('tr');

      const subtotal = it.price * it.qty;
      tr.innerHTML = `
        <td>${it.name}</td>
        <td style="text-align:center">
          <input data-idx="${idx}" class="qtyInput" type="number" value="${it.qty}" min="1" style="width:60px;padding:6px;border-radius:6px;border:1px solid #ddd;text-align:center;">
        </td>
        <td style="text-align:right">Rp ${numberFormat(it.price)}</td>
        <td style="text-align:right">Rp ${numberFormat(subtotal)}</td>
        <td style="text-align:center"><button data-idx="${idx}" class="removeBtn" style="background:#ef4444;color:#fff;border:none;padding:6px 8px;border-radius:6px;cursor:pointer;">Hapus</button></td>
      `;
      tbody.appendChild(tr);
      total += subtotal;
    });

    totalText.textContent = 'Rp ' + numberFormat(total);

    // attach events
    document.querySelectorAll('.removeBtn').forEach(b=>{
      b.addEventListener('click', e=>{
        const i = parseInt(e.target.dataset.idx);
        cart.splice(i,1);
        renderCart();
      });
    });

    document.querySelectorAll('.qtyInput').forEach(inp=>{
      inp.addEventListener('change', e=>{
        const i = parseInt(e.target.dataset.idx);
        const v = Math.max(1, parseInt(e.target.value) || 1);
        cart[i].qty = v;
        renderCart();
      });
    });
  }

  addBtn.addEventListener('click', () => {
    const pid = parseInt(productSelect.value);
    const price = parseInt(productSelect.selectedOptions[0].dataset.price);
    const name = productSelect.selectedOptions[0].text.split(' — Rp')[0] || productSelect.selectedOptions[0].text;
    const qty = Math.max(1, parseInt(qtyInput.value) || 1);

    const existing = cart.find(c => c.id === pid);
    if (existing) existing.qty += qty;
    else cart.push({ id: pid, name, price, qty });

    renderCart();
  });

  clearBtn.addEventListener('click', ()=>{
    if (!confirm('Kosongkan semua item di keranjang?')) return;
    cart = [];
    renderCart();
  });

  saveBtn.addEventListener('click', ()=>{
    if (cart.length === 0) { alert('Keranjang kosong. Tambahkan produk terlebih dahulu.'); return; }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    fetch('save_sale.php', {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify({ items: cart })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'ok') {
        alert('Transaksi berhasil disimpan (ID: ' + res.sale_id + ')');
        cart = [];
        renderCart();
      } else {
        alert('Gagal menyimpan: ' + (res.error || 'unknown'));
      }
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan jaringan.');
    })
    .finally(()=>{
      saveBtn.disabled = false;
      saveBtn.textContent = 'Simpan Transaksi';
    });
  });

  // initial
  renderCart();
});
