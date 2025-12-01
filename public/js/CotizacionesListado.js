document.addEventListener('DOMContentLoaded', () => {
    // 🎯 Escuchar todos los botones "Crear reserva"
    document.querySelectorAll('.btn-accion.btn-convertir').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;

            alertify.confirm(
                'Confirmar acción',
                '¿Deseas convertir esta cotización en una reservación?<br><br><strong>Se eliminará el PDF y el registro original.</strong>',
                async function () {
                    try {
                        alertify.message('⏳ Procesando conversión...', 3);

                        const response = await fetch(`/admin/cotizaciones/${id}/convertir`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            alertify.success('✅ Cotización convertida correctamente.');
                            setTimeout(() => {
                                alertify.notify(`🧾 Nueva reservación generada: <b>${data.codigo || '(sin código)'}</b>`, 'custom', 6);
                            }, 700);

                            setTimeout(() => {
                                alertify.message('📄 PDF eliminado y cotización removida.');
                            }, 1200);

                            const row = btn.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.5s';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 500);
                            }
                        } else {
                            alertify.error(`❌ No se pudo convertir la cotización: ${data.message || 'Error desconocido.'}`);
                            console.error(data.error || data.message);
                        }
                    } catch (err) {
                        console.error('Error:', err);
                        alertify.error('⚠️ Error interno al procesar la conversión.');
                    }
                },
                function () {
                    alertify.message('❎ Conversión cancelada.');
                }
            ).set('labels', { ok: 'Sí, convertir', cancel: 'Cancelar' });
        });
    });

    // 📨 Escuchar todos los botones "Reenviar correo"
    document.querySelectorAll('.btn-accion.btn-reenviar').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;

            alertify.confirm(
                'Reenviar cotización',
                '¿Deseas reenviar esta cotización al cliente?<br><br>Se enviará el PDF existente al correo registrado.',
                async function () {
                    try {
                        alertify.message('📨 Enviando correo...', 3);

                        const response = await fetch(`/admin/cotizaciones/${id}/reenviar`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            alertify.success('✅ Cotización reenviada correctamente.');
                            setTimeout(() => {
                                alertify.message(data.message || 'Correo enviado al cliente.');
                            }, 600);
                        } else {
                            alertify.error(`❌ No se pudo reenviar la cotización: ${data.message || 'Error desconocido.'}`);
                            console.error(data.error || data.message);
                        }
                    } catch (err) {
                        console.error('Error:', err);
                        alertify.error('⚠️ Error interno al reenviar el correo.');
                    }
                },
                function () {
                    alertify.message('❎ Reenvío cancelado.');
                }
            ).set('labels', { ok: 'Sí, reenviar', cancel: 'Cancelar' });
        });
    });

    // 🗑️ Escuchar todos los botones "Eliminar cotización"
    document.querySelectorAll('.btn-accion.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;

            alertify.confirm(
                'Eliminar cotización',
                '⚠️ ¿Seguro que deseas eliminar esta cotización?<br><br><strong>Se eliminará también el archivo PDF asociado.</strong>',
                async function () {
                    try {
                        alertify.message('🗑️ Eliminando cotización...', 3);

                        const response = await fetch(`/admin/cotizaciones/${id}/eliminar`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            alertify.success('✅ Cotización eliminada correctamente.');
                            setTimeout(() => {
                                alertify.message('📄 PDF eliminado y registro removido.');
                            }, 800);

                            // Eliminar fila visualmente
                            const row = btn.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.4s ease';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 400);
                            }
                        } else {
                            alertify.error(`❌ No se pudo eliminar la cotización: ${data.message || 'Error desconocido.'}`);
                            console.error(data.error || data.message);
                        }
                    } catch (err) {
                        console.error('Error:', err);
                        alertify.error('⚠️ Error interno al eliminar la cotización.');
                    }
                },
                function () {
                    alertify.message('❎ Eliminación cancelada.');
                }
            ).set('labels', { ok: 'Sí, eliminar', cancel: 'Cancelar' });
        });
    });
});
