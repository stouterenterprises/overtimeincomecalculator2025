(() => {
  if (!window.wp || !window.wp.blocks) {
    return;
  }

  const { registerBlockType } = window.wp.blocks;
  const { createElement } = window.wp.element;

  registerBlockType('odc25/calculator', {
    edit() {
      return createElement('p', null, '2025 Overtime Deduction Calculator will render on the front end.');
    },
    save() {
      return null;
    }
  });
})();
