function loadPhrases() {
  const theme = document.getElementById('theme').value;
  const section = document.getElementById('practiceType').value;

  fetch(`api/get_phrases.php?theme=${theme}&section=${section}`)
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('phrases');
      container.innerHTML = `<h2>??? Practice Phrases – ${section === 'section_a' ? 'Section A' : 'General'}</h2>`;

      if (data.length === 0) {
        container.innerHTML += `<p>Aucune phrase disponible pour ce thème.</p>`;
        return;
      }

      data.forEach(({ french_text, english_translation }) => {
        const div = document.createElement('div');
        div.className = 'phrase-box';
        div.innerHTML = `
          <p><strong>Français:</strong> ${french_text}</p>
          <p><strong>Anglais:</strong> ${english_translation}</p>
          <button onclick="speak('${french_text.replace(/'/g, "\\'")}')">?? Écouter</button>
          <button onclick="recordSpeech('${french_text.replace(/'/g, "\\'")}')">??? Enregistrer</button>
          <div class="result"></div>
        `;
        container.appendChild(div);
      });
    });
}

document.getElementById('theme').addEventListener('change', loadPhrases);
document.getElementById('practiceType').addEventListener('change', loadPhrases);
window.addEventListener('load', loadPhrases);
