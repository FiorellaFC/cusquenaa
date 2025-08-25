// js/functions/pagination.js

(function() {
    // Variables de estado
    let currentPage = 1;
    let itemsPerPage = 10; // Valor por defecto
    let data = [];
    let onPageChangeCallback = null;
  
    // Selecciona elementos del DOM
    const paginationElement = document.getElementById('pagination');
    
    // Si no existe el elemento, termina la ejecución
    if (!paginationElement) return;
    
    // Configuración inicial
    const prevButton = paginationElement.querySelector('#prev-page');
    const nextButton = paginationElement.querySelector('#next-page');
    
    // Función para actualizar la paginación
    function updatePagination() {
        const totalPages = Math.ceil(data.length / itemsPerPage);
        
        // Limpiar números de página existentes
        const pageNumbers = paginationElement.querySelectorAll('.page-number');
        pageNumbers.forEach(el => el.remove());
        
        // Crear nuevos números de página
        const nextBtnParent = nextButton ? nextButton.parentElement : null;
        
        if (nextBtnParent) {
            for (let i = 1; i <= totalPages; i++) {
                const pageItem = document.createElement('li');
                pageItem.className = `page-item page-number ${i === currentPage ? 'active' : ''}`;
                pageItem.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                nextBtnParent.before(pageItem);
            }
        }
        
        // Actualizar estado de botones
        if (prevButton) {
            prevButton.parentElement.classList.toggle('disabled', currentPage === 1);
        }
        if (nextButton) {
            nextButton.parentElement.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
        }
    }
    
    // Manejador de eventos
    function handlePaginationClick(e) {
        e.preventDefault();
        const target = e.target.closest('a');
        if (!target) return;
        
        const totalPages = Math.ceil(data.length / itemsPerPage);
        
        if (target.id === 'prev-page' && currentPage > 1) {
            currentPage--;
        } else if (target.id === 'next-page' && currentPage < totalPages) {
            currentPage++;
        } else if (target.dataset.page) {
            currentPage = parseInt(target.dataset.page);
        }
        
        updatePagination();
        
        // Llamar al callback si existe
        if (onPageChangeCallback && typeof onPageChangeCallback === 'function') {
            onPageChangeCallback({
                currentPage,
                itemsPerPage,
                totalItems: data.length,
                totalPages: Math.ceil(data.length / itemsPerPage)
            });
        }
    }
    
    // API pública accesible desde window
    window.Pagination = {
        init: function(config) {
            if (config.data) data = config.data;
            if (config.itemsPerPage) itemsPerPage = config.itemsPerPage;
            if (config.onPageChange) onPageChangeCallback = config.onPageChange;
            
            updatePagination();
            paginationElement.addEventListener('click', handlePaginationClick);
        },
        
        updateData: function(newData) {
            data = newData;
            currentPage = 1; // Reset a la primera página
            updatePagination();
        },
        
        setItemsPerPage: function(newItemsPerPage) {
            itemsPerPage = newItemsPerPage;
            currentPage = 1; // Reset a la primera página
            updatePagination();
        },
        
        getCurrentState: function() {
            return {
                currentPage,
                itemsPerPage,
                totalItems: data.length,
                totalPages: Math.ceil(data.length / itemsPerPage)
            };
        }
    };
  })();