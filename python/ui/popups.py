import os
import tkinter as tk
import ttkbootstrap as tb
from ttkbootstrap.constants import *

def center_window(popup: tk.Toplevel, parent: tk.Tk) -> None:
    """Centre une popup par rapport à sa fenêtre parente."""
    popup.update_idletasks()
    parent_x = parent.winfo_rootx()
    parent_y = parent.winfo_rooty()
    parent_w = parent.winfo_width()
    parent_h = parent.winfo_height()
    popup_w = popup.winfo_reqwidth()
    popup_h = popup.winfo_reqheight()
    x = parent_x + (parent_w // 2) - (popup_w // 2)
    y = parent_y + (parent_h // 2) - (popup_h // 2)
    popup.geometry(f"+{x}+{y}")

def create_popup(parent: tk.Tk, title: str) -> tk.Toplevel:
    """Crée une popup standardisée avec le titre donné."""
    popup = tk.Toplevel(parent)
    popup.withdraw()
    popup.title(title)
    popup.resizable(False, False)
    popup.transient(parent)
    script_dir = os.path.dirname(__file__)
    assets_dir = os.path.join(script_dir, "..", "assets")
    icon_path = os.path.join(assets_dir, "logo-icon.ico")
    if os.path.exists(icon_path):
        try:
            popup.iconbitmap(icon_path)
        except Exception as e:
            print("Erreur lors du chargement de l'icône dans la popup:", e)
    return popup

def _display_popup(popup: tk.Toplevel, parent: tk.Tk) -> None:
    """Affiche la popup en la centrant et en bloquant la fenêtre parente."""
    center_window(popup, parent)
    popup.deiconify()
    popup.grab_set()
    popup.focus_force()
    popup.wait_window()

def _build_popup(parent: tk.Tk, title: str, message: str) -> tk.Toplevel:
    """Construit la popup avec le message principal."""
    popup = create_popup(parent, title)
    label = tb.Label(popup, text=message, font=("Helvetica", 11), padding=10)
    label.pack(padx=20, pady=10)
    return popup

def _show_popup(
    parent: tk.Tk, 
    title: str, 
    message: str, 
    buttons: list
) -> None:
    """
    Affiche une popup générique.
    
    :param buttons: Liste de tuples (texte, bootstyle, commande) pour créer les boutons.
    """
    popup = _build_popup(parent, title, message)
    btn_frame = tb.Frame(popup)
    btn_frame.pack(pady=10)
    for text, bootstyle, command in buttons:
        btn = tb.Button(
            btn_frame, 
            text=text, 
            bootstyle=bootstyle, 
            command=lambda cmd=command, p=popup: cmd(p), 
            takefocus=False
        )
        btn.pack(side=tk.LEFT, padx=10)
    _display_popup(popup, parent)

def my_showinfo(parent: tk.Tk, title: str, message: str) -> None:
    """Affiche une popup d'info avec un bouton OK."""
    _show_popup(parent, title, message, [("OK", "success", lambda popup: popup.destroy())])

def my_showwarning(parent: tk.Tk, title: str, message: str) -> None:
    """Affiche une popup d'avertissement avec un bouton OK."""
    _show_popup(parent, title, message, [("OK", "warning", lambda popup: popup.destroy())])

def my_showerror(parent: tk.Tk, title: str, message: str) -> None:
    """Affiche une popup d'erreur avec un bouton OK."""
    _show_popup(parent, title, message, [("OK", "danger", lambda popup: popup.destroy())])

def my_askyesno(parent: tk.Tk, title: str, message: str) -> bool:
    """Affiche une popup de confirmation Oui/Non et retourne True si Oui."""
    answer = {"value": False}

    def yes(popup: tk.Toplevel) -> None:
        answer["value"] = True
        popup.destroy()

    def no(popup: tk.Toplevel) -> None:
        answer["value"] = False
        popup.destroy()

    _show_popup(parent, title, message, [("Oui", "success", yes), ("Non", "danger", no)])
    return answer["value"]