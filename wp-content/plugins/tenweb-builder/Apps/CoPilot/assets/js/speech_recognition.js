class TWBBSpeechRecognition {
    init() {
        let self = this;
        this.isRecording = false;
        this.audioContext;
        this.analyser;
        this.dataArray;
        this.max_size =0;
        this.source;
        this.animationId;
        this.startTime;
        this.timerInterval;
        this.silenceTimer;
        this.lastVoiceActivity;
        this.recognition;
        this.waveform;
        this.speechToTextDisplay = '';
        this.state = 'stop';

        this.createBars();

        jQuery(document).on("click","#twbb-copilot-speech-to-text", async function(e) {
            if(jQuery('#twbb-copilot-footer').hasClass('twbb_chat_in_progress')){
                e.preventDefault();
                return;
            }
            if(!self.isRecording){
                analyticsDataPush('Voice to text icon click', 'Co-pilot chat');
                await self.startRecording();
            }else{
                self.stopRecording();
            }

        });
        //twbb_cancel_recording
        jQuery(document).on("click",".twbb_cancel_recording", async function() {
            this.speechToTextDisplay = '';
            self.stopRecording('cancel');
        });
        jQuery(document).on("click",".twbb_stop_recording", async function() {
            setTimeout(function (){
                self.stopRecording();
            },200);
        });
        jQuery(document).on("keydown", function (e) {
            if(self.isRecording){
                if (e.key === "Escape") {
                    self.stopRecording('cancel');
                }
                if (e.key === "Enter") {
                    setTimeout(function (){
                        self.stopRecording();
                    },200);
                }
            }
        });

    }
    createBars() {
        jQuery('.twbb-bar').addClass('twbb-bar_disabled');
        jQuery('.twbb-bar').removeClass('twbb-bar');
        this.waveform = jQuery('.twbb-speech-to-text-waveform');
        let bars_count = jQuery('.twbb-speech-to-text-waveform span:not(.twbb-bar-empty)').length;
        if(bars_count === 0){
            for (let i = 0; i < 84; i++) {
                jQuery('<span>', {
                    class: 'twbb-bar-empty',
                }).appendTo(this.waveform);
            }
        }
        if(bars_count > 41){
            bars_count = 41
        }
        for (let i = 0; i < 1; i++) {
            jQuery('<span>', {
                class: 'twbb-bar',
            }).appendTo(this.waveform);
        }
        if (jQuery('.twbb-bar_disabled').length > 168) {
            jQuery('.twbb-bar_disabled').slice(0, 84).remove();
        }
        if(this.max_size>0){
            let height = this.max_size / 255 * 100;
            this.max_size = 0;
            height = Math.max(10, Math.min(height, 100));
            if(height>10){
                jQuery('.twbb-bar').css('height', `${height}%`);
            }
        }
    }
    async updateTimer () {
        let timeInSeconds = Math.floor((Date.now() - this.startTime) / 1000);
        let minutes = Math.floor(timeInSeconds / 60);
        if(minutes < 10){
            minutes = "0" + minutes;
        }
        const seconds = timeInSeconds % 60;
        jQuery('.twbb_recording_timer').html(`${minutes}:${seconds.toString().padStart(2, '0')}`);
    };

    async startRecording () {
        if(this.isRecording){
            return;
        }
        try {
            let that = this;
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            jQuery('#twbb-copilot-footer').addClass('twbb_voice_active');
            this.analyser = this.audioContext.createAnalyser();
            this.source = this.audioContext.createMediaStreamSource(stream);
            this.source.connect(this.analyser);
            //this.analyser.fftSize = 2048;
            this.analyser.fftSize = 512;
            this.dataArray = new Uint8Array(this.analyser.frequencyBinCount);
            this.updateWaveform = this.updateWaveform.bind(this);
            this.updateWaveform();
            this.createBarsInterval = setInterval(function (){
                that.createBars();
            },100);
            this.isRecording = true;
            this.startTime = Date.now();
            this.timerInterval = setInterval(() => this.updateTimer(), 1000);
            this.lastVoiceActivity = Date.now();



            // Start speech recognition
            if ('webkitSpeechRecognition' in window) {
                this.recognition = new webkitSpeechRecognition();
                this.recognition.lang = 'en-US';
                this.recognition.interimResults = true;
                this.recognition.continuous = true;

                this.recognition.onstart = () => {
                    jQuery('#twbb-copilot-footer').addClass('twbb-speech-to-text-active');
                    console.log('Speech recognition started');
                };

                this.recognition.onresult = (event) => {
                    const transcript = Array.from(event.results)
                        .map(result => result[0].transcript)
                        .join('');
                    console.log('Transcript:', transcript);
                    this.speechToTextDisplay = transcript;
                };

                this.recognition.onerror = async (event) => {
                    console.error('Speech recognition error', event.error);
                    setTimeout(() => {
                        if(this.isRecording){
                            this.recognition.start();
                        }
                    }, 100);
                };

                this.recognition.onend = () => {
                    console.log('Speech recognition ended');
                    if(this.isRecording){
                        this.recognition.start();
                    }
                    if(!this.isRecording && this.state!='cancel'){
                        let current_val = jQuery('#twbb-copilot-user_input').val();
                        if(current_val.length>0){
                            let new_val = current_val+ ' ' + this.speechToTextDisplay;
                            jQuery('#twbb-copilot-user_input').val(new_val);
                        }else{
                            jQuery('#twbb-copilot-user_input').val(this.speechToTextDisplay);
                        }

                        jQuery('#twbb-copilot-user_input').trigger('input');
                        this.speechToTextDisplay = '';
                    }
                };

                this.recognition.start();
            } else {
                console.error('Speech recognition not supported');
            }
        } catch (error) {
            jQuery('#twbb-copilot-footer').addClass('twbb_voice_disabled');
            console.error('Error accessing microphone:', error);
            //errorMessageDisplay.textContent = `Error accessing microphone: ${error.message}`;
        }
    };
    stopRecording (state = 'stop') {
        this.state = state;
        jQuery('.twbb_recording_timer').html('00:00');
        jQuery('#twbb-copilot-footer').removeClass('twbb_voice_active');
        jQuery('.twbb-speech-to-text-active').removeClass('twbb-speech-to-text-active');
        if (this.source) {
            this.source.disconnect();
        }
        if (this.audioContext) {
            this.audioContext.close();
        }
        cancelAnimationFrame(this.animationId);
        clearInterval(this.timerInterval);
        this.isRecording = false;
        this.createBars();

        if (this.recognition) {
            this.recognition.stop();
        }
        if(state === 'cancel'){
            this.speechToTextDisplay = '';
        }
        clearInterval(this.createBarsInterval);
        this.waveform.empty();
        jQuery("#twbb-copilot-user_input").focus();
    };

    updateWaveform() {
        let self = this;
        if (!this.analyser) return;  // Prevent errors if `this.analyser` is undefined
        this.analyser.getByteFrequencyData(this.dataArray);
        const bars = this.waveform.find('.twbb-bar').toArray().reverse(); // Fix incorrect selector

        let voiceDetected = false;

        bars.forEach((bar, i) => {
            const barIndex = Math.floor(i * this.analyser.frequencyBinCount / bars.length);
            let height = this.dataArray[barIndex] / 255;
            if(this.max_size < this.dataArray[barIndex]){
                this.max_size = this.dataArray[barIndex];
            }
        });

        if (voiceDetected) {
            this.lastVoiceActivity = Date.now();
        }

        this.animationId = requestAnimationFrame(this.updateWaveform); // Ensure proper context
    }



}


