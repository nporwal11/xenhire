function logout() {
    document.cookie = "xenhire_candidate_logged_in=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "xenhire_candidate_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    window.location.href = (typeof xenhireAjax !== 'undefined') ? xenhireAjax.login_url : '/candidate-login/';
}

function startInterview() {
    // Get Job ID and Application ID (jid) from URL
    const urlParams = new URLSearchParams(window.location.search);
    const jid = urlParams.get('jid');
    const jobId = (typeof xenhireBeforeData !== 'undefined') ? xenhireBeforeData.jobId : '';
    const interviewUrl = (typeof xenhireBeforeData !== 'undefined') ? xenhireBeforeData.interviewUrl : '/interview/';

    if (jid) {
        window.location.href = interviewUrl + jobId + '/?jid=' + jid;
    } else {
        alert('Application ID missing. Please ensure you have applied for this job.');
    }
}

// Microphone Test Logic
const startBtn = document.getElementById('start-mic');
const stopBtn = document.getElementById('stop-mic');
const micBar = document.getElementById('mic-bar');
const micStatus = document.getElementById('mic-status');
let audioContext;
let analyser;
let microphone;
let javascriptNode;

startBtn.addEventListener('click', async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioContext = new AudioContext();
        analyser = audioContext.createAnalyser();
        microphone = audioContext.createMediaStreamSource(stream);
        javascriptNode = audioContext.createScriptProcessor(2048, 1, 1);

        analyser.smoothingTimeConstant = 0.8;
        analyser.fftSize = 1024;

        microphone.connect(analyser);
        analyser.connect(javascriptNode);
        javascriptNode.connect(audioContext.destination);

        javascriptNode.onaudioprocess = function () {
            var array = new Uint8Array(analyser.frequencyBinCount);
            analyser.getByteFrequencyData(array);
            var values = 0;
            var length = array.length;
            for (var i = 0; i < length; i++) {
                values += (array[i]);
            }
            var average = values / length;
            micBar.style.width = Math.min(100, average * 2) + '%';
        }

        micStatus.textContent = 'Microphone is working properly.';
        micStatus.style.color = 'green';
    } catch (err) {
        console.error(err);
        micStatus.textContent = 'Microphone access denied or error: ' + err.message;
        micStatus.style.color = 'red';
    }
});

stopBtn.addEventListener('click', () => {
    if (audioContext) {
        audioContext.close();
        micBar.style.width = '0%';
        micStatus.textContent = 'Microphone test stopped.';
        micStatus.style.color = '#6b7280';
    }
});

