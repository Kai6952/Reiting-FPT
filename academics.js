document.addEventListener('DOMContentLoaded', () => {
    const listItems = document.querySelectorAll('.academics-l-link');
  
    listItems.forEach(item => {
      const ratingElement = item.querySelector('.academic-t-container p span');
      const value = parseFloat(ratingElement.textContent);
  
      // Находим именно элемент <span>, у которого нужно изменить цвет
      const spanElement = item.querySelector('.academic-t-container p span');
  
      if (value > 4.5) {
        spanElement.style.color = '#36F87D'; // Зеленый
      } else if (value >= 3) {
        spanElement.style.color = '#F8B436'; // Желтый
      } else if (value > 0) {
        spanElement.style.color = '#F83636'; // Красный
      } else {
        spanElement.style.color = '#6E6E6E';
      }
    });
  });