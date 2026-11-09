/** Toggle visibilitas password untuk field yang menggunakan x-data="passwordToggle". */
export default () => ({
    visible: false,
    toggle() { this.visible = !this.visible; },
});
