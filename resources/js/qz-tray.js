/**
 * QZ Tray Integration for Thermal Printer
 *
 * This module provides integration with QZ Tray for direct thermal printing.
 * QZ Tray must be installed on the client machine: https://qz.io/download/
 */

window.QZTrayPrinter = {
    // State
    connected: false,
    printerName: null,
    initialized: false,

    /**
     * Initialize QZ Tray connection
     */
    async init() {
        if (this.initialized) {
            return this.connected;
        }

        // Check if QZ Tray is available
        if (typeof qz === 'undefined') {
            console.warn('[QZ] QZ Tray library not loaded');
            return false;
        }

        try {
            // Set up certificate (for signed requests - optional for localhost)
            qz.security.setCertificatePromise(function(resolve, reject) {
                // For development, we'll use an empty promise
                // In production, you should provide a valid certificate
                resolve();
            });

            // Set up signature (for signed requests - optional for localhost)
            qz.security.setSignaturePromise(function(toSign) {
                return function(resolve, reject) {
                    // For development, we'll skip signing
                    resolve();
                };
            });

            // Connect to QZ Tray
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect();
            }

            this.connected = true;
            this.initialized = true;

            // Load saved printer name from localStorage
            this.printerName = localStorage.getItem('qz_printer_name');

            console.log('[QZ] Connected to QZ Tray');
            return true;
        } catch (error) {
            console.error('[QZ] Failed to connect:', error);
            this.connected = false;
            this.initialized = true;
            return false;
        }
    },

    /**
     * Check if QZ Tray is connected
     */
    isConnected() {
        return this.connected && qz?.websocket?.isActive();
    },

    /**
     * Get list of available printers
     */
    async getPrinters() {
        if (!await this.init()) {
            throw new Error('QZ Tray tidak terhubung');
        }

        try {
            const printers = await qz.printers.find();
            return printers;
        } catch (error) {
            console.error('[QZ] Failed to get printers:', error);
            throw error;
        }
    },

    /**
     * Set the default printer
     */
    setPrinter(printerName) {
        this.printerName = printerName;
        localStorage.setItem('qz_printer_name', printerName);
        console.log('[QZ] Printer set to:', printerName);
    },

    /**
     * Get the current printer name
     */
    getPrinter() {
        return this.printerName;
    },

    /**
     * Print raw ESC/POS commands
     */
    async printRaw(commands) {
        if (!await this.init()) {
            throw new Error('QZ Tray tidak terhubung. Pastikan QZ Tray sudah terinstall dan berjalan.');
        }

        if (!this.printerName) {
            throw new Error('Printer belum dipilih. Silakan pilih printer di pengaturan.');
        }

        try {
            const config = qz.configs.create(this.printerName, {
                encoding: 'UTF-8'
            });

            // Convert commands array to proper format
            const data = this.prepareCommands(commands);

            await qz.print(config, data);
            console.log('[QZ] Print successful');
            return true;
        } catch (error) {
            console.error('[QZ] Print failed:', error);
            throw error;
        }
    },

    /**
     * Prepare ESC/POS commands for QZ Tray
     */
    prepareCommands(commands) {
        if (typeof commands === 'string') {
            return [commands];
        }

        if (Array.isArray(commands)) {
            return commands.map(cmd => {
                // Convert escaped hex strings to actual bytes
                if (typeof cmd === 'string') {
                    return cmd.replace(/\\x([0-9A-Fa-f]{2})/g, (match, hex) => {
                        return String.fromCharCode(parseInt(hex, 16));
                    });
                }
                return cmd;
            });
        }

        return commands;
    },

    /**
     * Print a sale receipt by fetching ESC/POS commands from server
     */
    async printReceipt(saleId) {
        try {
            // Fetch ESC/POS commands from server
            const response = await fetch(`/pos/receipts/${saleId}/escpos-json`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data struk');
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal mengambil data struk');
            }

            // Print the commands
            await this.printRaw(data.data.commands);
            return true;
        } catch (error) {
            console.error('[QZ] Print receipt failed:', error);
            throw error;
        }
    },

    /**
     * Print test page
     */
    async printTest() {
        const commands = [
            '\x1B\x40',          // Initialize
            '\x1B\x61\x01',      // Center align
            '\x1B\x21\x10',      // Double height
            'TEST PRINT\n',
            '\x1B\x21\x00',      // Normal
            '================================\n',
            '\x1B\x61\x00',      // Left align
            'Printer: ' + (this.printerName || 'Unknown') + '\n',
            'Waktu: ' + new Date().toLocaleString('id-ID') + '\n',
            '================================\n',
            '\x1B\x61\x01',      // Center
            'QZ Tray Connected!\n',
            '\n\n\n',
            '\x1D\x56\x00',      // Cut paper
        ];

        return await this.printRaw(commands);
    },

    /**
     * Disconnect from QZ Tray
     */
    async disconnect() {
        if (qz?.websocket?.isActive()) {
            await qz.websocket.disconnect();
        }
        this.connected = false;
        this.initialized = false;
        console.log('[QZ] Disconnected');
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if QZ Tray library is loaded
    if (typeof qz !== 'undefined') {
        QZTrayPrinter.init().then(connected => {
            if (connected) {
                console.log('[QZ] Auto-initialized successfully');
            }
        }).catch(err => {
            console.warn('[QZ] Auto-init failed:', err);
        });
    }
});
