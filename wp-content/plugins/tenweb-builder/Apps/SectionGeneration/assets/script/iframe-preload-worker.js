// Web Worker for preloading iframe content
self.onmessage = async function(e) {
    if (e.data.type === 'preload') {
        try {
            const response = await fetch(e.data.url);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Network response was not ok. Status: ${response.status}, Response: ${errorText}`);
            }
            
            const htmlContent = await response.text();

            self.postMessage({
                type: 'success',
                content: htmlContent
            });
        } catch (error) {
            self.postMessage({
                type: 'error',
                error: error.message || 'Unknown error occurred in worker'
            });
        }
    }
}; 