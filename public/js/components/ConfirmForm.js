export default class ConfirmForm extends HTMLFormElement {
  constructor() {
    super();
    const { buttonText, confirmText } = this.dataset;
    this.method = "POST";
    delete this.dataset.buttonText;
    delete this.dataset.confirmText;
    this.innerHTML = `<input type="submit" value="${buttonText}" />`;
    this.addEventListener("submit", e => {
      e.preventDefault();

      const confirmation = confirm(confirmText);
      if (!confirmation)
        return;
      this.submit();
    });
  }
}