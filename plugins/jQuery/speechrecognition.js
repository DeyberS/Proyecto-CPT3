      const startBtn = document.getElementById("start-btn");
      const stopBtn = document.getElementById("stop-btn");


      let textFields = document.querySelectorAll('input[type="text"], textarea');
      let currentField = null;
      let recognition = null;

      
      textFields.forEach((field) => {
        field.addEventListener('focus', (event) => {
          currentField = event.target;
        });
      });

      if ('webkitSpeechRecognition' in window) 
      {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = false;
        recognition.lang = 'es-ES';


        recognition.onresult = function(event) 
        {
          let interimTranscript = '';
          let finalTranscript = '';


          for (let i = event.resultIndex; i < event.results.length; i++) 
          {
            let transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) 
            {
              finalTranscript += transcript;
            } 
            else 
            {
              interimTranscript += transcript;
            }
          }
         
          if (currentField) 
          {
            if (currentField.tagName === 'TEXTAREA') 
            {
              currentField.value += finalTranscript;
            } 
            else 
            {
              currentField.focus();
              currentField.value += finalTranscript;
            }
          }
        };

        recognition.onend = function() 
        {
          startBtn.disabled = false;
          stopBtn.disabled = true;
        };
        
      }


      function Grabar() 
      {    
        recognition.start();
        startBtn.disabled = true;
        stopBtn.disabled = false;
      }

      function PararGrabacion() 
      {    
        recognition.stop();
        startBtn.disabled = false;
        stopBtn.disabled = true;
      }