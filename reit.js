for (let i = 1; i <= 5; i++) {
    const slider = document.getElementById(`crit${i}`);
    const output = document.getElementById(`val${i}`);
  
    const update = () => {
      const val = parseInt(slider.value);
      output.textContent = val;
  
      let color = '#00ECDC';
      if (val === 5) color = '#00FF7F';
      else if (val === 4) color = '#00ECDC';
      else if (val === 3) color = '#FFA500';
      else if (val === 2) color = '#FF6347';
      else color = '#FF3333';
  
      slider.style.setProperty('--thumb-color', color);
      slider.style.setProperty('--fill', `${((val - 1) / 4) * 100}%`);
      output.style.color = '#d0d0d0';
    };
  
    slider.addEventListener('input', update);
    update();
  }
  