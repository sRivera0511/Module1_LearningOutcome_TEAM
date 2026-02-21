const form = document.getElementById('order-form');
const result = document.getElementById('result');

// Mapea cada estado a una clase CSS para pintar el badge con color.
const statusClassMap = {
  'Ordered': 'badge-ordered',
  'In process': 'badge-in-process',
  'In route': 'badge-in-route',
  'Delivered': 'badge-delivered'
};

form?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const customerNumber = document.getElementById('customer-number').value.trim();
  const invoiceNumber = document.getElementById('invoice-number').value.trim();

  // Feedback inmediato mientras se consulta el backend.
  result.innerHTML = '<div class="status-card"><p class="result-message">Consultando pedido...</p></div>';

  try {
    // Envia los datos del formulario al endpoint público.
    const res = await fetch('api/public_lookup.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        customer_number: customerNumber,
        invoice_number: invoiceNumber
      })
    });

    const data = await res.json();

    if (!data.ok) {
      result.innerHTML = `<div class="status-card"><p class="result-message result-error">${data.message}</p></div>`;
      return;
    }

    const badgeClass = statusClassMap[data.status] || 'badge-default';

    let html = `
      <div class="status-card">
        <p class="status-title">Estado actual del pedido</p>
        <span class="status-badge ${badgeClass}">${data.status}</span>
      </div>
    `;

    // Solo muestra evidencia visual cuando el pedido está entregado.
    if (data.status === 'Delivered' && data.delivery_photo) {
      html = `
        <div class="status-card">
          <p class="status-title">Estado actual del pedido</p>
          <span class="status-badge ${badgeClass}">${data.status}</span>
          <img src="${data.delivery_photo}" alt="Evidencia de entrega" class="status-photo">
        </div>
      `;
    }

    result.innerHTML = html;
  } catch (error) {
    // Error de red o respuesta inválida.
    result.innerHTML = '<div class="status-card"><p class="result-message result-error">No se pudo consultar el pedido. Intenta de nuevo.</p></div>';
  }
});
