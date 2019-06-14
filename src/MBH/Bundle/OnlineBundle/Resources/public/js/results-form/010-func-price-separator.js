function mbhFuncPriceSeparator (amount) {
    amount = String(amount);
    if (amount.length <= 3) {
        return amount;
    } else if (amount.length <= 6) {
        return amount.replace(/(\d{3}$)/, " $1");
    } else if (amount.length <= 9) {
        return (amount.replace(/(\d{3})(\d{3}$)/, " $1 $2"));
    } else if (amount.length <= 12) {
        return (amount.replace(/(\d{3})(\d{3})(\d{3}$)/, " $1 $2 $3"));
    } else {
        return (amount.replace(/(\d)(\d{3})(\d{3})(\d{3}$)/, "$1 $2 $3 $4"));
    }
};
