function encrypt(string, key) {
        var encryptMethod = "AES-256-CBC";
        var encryptMethodLength = parseInt(encryptMethod.match(/\d+/)[0]);
        var iv = CryptoJS.lib.WordArray.random(16);// the reason to be 16, please read on `encryptMethod` property.

        var salt = CryptoJS.lib.WordArray.random(256);
        var iterations = 999;
        encryptMethodLength = (encryptMethodLength/4);// example: AES number is 256 / 4 = 64
        var hashKey = CryptoJS.PBKDF2(key, salt, {'hasher': CryptoJS.algo.SHA512, 'keySize': (encryptMethodLength/8), 'iterations': iterations});

        var encrypted = CryptoJS.AES.encrypt(string, hashKey, {'mode': CryptoJS.mode.CBC, 'iv': iv});
        var encryptedString = CryptoJS.enc.Base64.stringify(encrypted.ciphertext);

        var output = {
            'ciphertext': encryptedString,
            'iv': CryptoJS.enc.Hex.stringify(iv),
            'salt': CryptoJS.enc.Hex.stringify(salt),
            'iterations': iterations
        };
        return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(output)));
    }// encrypt




function paddingsalt(hash)
    {
        var text = "";
        var length = 5;
        var possible = "0123456789";
       
        for (var i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
       
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
       
        for (var i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }
