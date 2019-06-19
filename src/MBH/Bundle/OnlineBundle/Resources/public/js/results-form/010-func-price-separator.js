function mbhFuncPriceSeparator (amount) {
    amount = String(amount);

    var splitNumber = amount.split('.'),
        lengthInteger = splitNumber[0].length;

    if (lengthInteger <= 3) {
    } else if (lengthInteger <= 6) {
        splitNumber[0] = splitNumber[0].replace(/(\d{3}$)/, " $1");
    } else if (lengthInteger <= 9) {
        splitNumber[0] = splitNumber[0].replace(/(\d{3})(\d{3}$)/, " $1 $2");
    } else if (lengthInteger <= 12) {
        splitNumber[0] = splitNumber[0].replace(/(\d{3})(\d{3})(\d{3}$)/, " $1 $2 $3");
    } else {
        splitNumber[0] = splitNumber[0].replace(/(\d)(\d{3})(\d{3})(\d{3}$)/, "$1 $2 $3 $4");
    }

    if (typeof splitNumber[1] !== 'undefined' &&  parseInt(splitNumber[1]) === 0) {
        return splitNumber[0];
    }

    return splitNumber.join('.');
};
