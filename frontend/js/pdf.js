function exportToPDF() {
    const { jsPDF } = window.jspdf;
      // Crear un PDF en orientación horizontal
      const doc = new jsPDF({
        orientation: 'landscape', // Orientación horizontal
        unit: 'mm', // Unidades en milímetros
        format: 'a4' // Formato A4
    });

    // Título del PDF
    doc.setFontSize(18);
    doc.text("Reporte de Balances - La Cusqueña", 10, 10, {aling: "center"});


    // Obtener los datos de la tabla
    const table = document.getElementById('balancesTable');
    const rows = table.querySelectorAll('tr');

    // Configurar el tamaño de la fuente para la tabla
    doc.setFontSize(11);
     // Definir el ancho de las columnas
     const columnWidths = [37, 30, 30, 30, 30, 35, 30, 20, 20];

     // Recorrer las filas de la tabla
     let y = 20; // Posición vertical inicial
     rows.forEach((row, index) => {
         const cells = row.querySelectorAll('th, td');
         let x = 18; // Posición horizontal inicial
 
         // Recorrer las celdas de la fila
         cells.forEach((cell, cellIndex) => {
             const text = cell.innerText;
             doc.text(text, x, y);
             x += columnWidths[cellIndex]; // Ajustar la posición horizontal según el ancho de la columna
         });
 
         y += 10; // Espacio entre filas
     });
 
     // Guardar el PDF
     doc.save('balances.pdf');
 }