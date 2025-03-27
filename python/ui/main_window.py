import os
import tkinter as tk
from tkinter import Text, ttk
import ttkbootstrap as tb
from ttkbootstrap.constants import *
from db.connection import Database
from ui.popups import my_showinfo, my_showwarning, my_showerror, my_askyesno, create_popup

class MainWindow(tb.Window):
    """Fenêtre principale de l'application PokéJob - Messagerie."""
    def __init__(self) -> None:
        """Initialise la fenêtre, configure l'interface et la connexion à la BDD."""
        super().__init__(themename="flatly")
        self.title("PokéJob - Messagerie")
        self.geometry("1000x600")
        self._load_icon()  # Charge l'icône de la fenêtre

        self.db = Database()  # 🔥 Connexion auto avec config Docker dans connection.py
        self._setup_menu()
        self._setup_top_frame()
        self._setup_main_frame()
        self._setup_bottom_frame()
        self.load_data()

    def _load_icon(self) -> None:
        script_dir = os.path.dirname(__file__)
        assets_dir = os.path.join(script_dir, "..", "assets")
        icon_path = os.path.join(assets_dir, "logo-icon.ico")
        if os.path.exists(icon_path):
            try:
                self.iconbitmap(icon_path)
            except Exception as e:
                print("Erreur lors du chargement de l'icône:", e)

    def _setup_menu(self) -> None:
        menubar = tb.Menu(self)
        self.config(menu=menubar)

    def _setup_top_frame(self) -> None:
        top_frame = tb.Frame(self, padding=10)
        top_frame.pack(side=tk.TOP, fill=tk.X, padx=15, pady=15)
        
        search_label = tb.Label(top_frame, text="Recherche :", font=("Helvetica", 12, "bold"))
        search_label.pack(side=tk.LEFT, padx=(0, 5))
        self.search_var = tk.StringVar()
        search_entry = tb.Entry(top_frame, textvariable=self.search_var, bootstyle="info", width=40)
        search_entry.pack(side=tk.LEFT, padx=(0, 5))
        search_entry.bind("<Return>", lambda e: self.load_data())
        search_btn = tb.Button(top_frame, text="Chercher", command=self.load_data, bootstyle="primary", takefocus=False)
        search_btn.pack(side=tk.LEFT, padx=(0, 10))
        
        filter_label = tb.Label(top_frame, text="Filtrer par statut :", font=("Helvetica", 12, "bold"))
        filter_label.pack(side=tk.LEFT, padx=(0, 5))
        self.status_filter_var = tk.StringVar(value="Tous")
        self.status_filter = ttk.Combobox(top_frame, textvariable=self.status_filter_var, state="readonly", width=15)
        self.status_filter["values"] = ("Tous", "Non lu", "Lu")
        self.status_filter.pack(side=tk.LEFT)
        self.status_filter.bind("<<ComboboxSelected>>", lambda e: self.load_data())
        
        self.unread_label = tb.Label(top_frame, text="Non lus : 0", font=("Helvetica", 12, "bold"))
        self.unread_label.pack(side=tk.RIGHT, padx=(10, 0))

    def _setup_main_frame(self) -> None:
        main_frame = tb.Frame(self, padding=10)
        main_frame.pack(side=tk.TOP, fill=tk.BOTH, expand=True, padx=15, pady=10)
        
        list_frame = tb.Frame(main_frame)
        list_frame.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 10))
        columns = ("from", "subject", "status")
        self.messages_list = tb.Treeview(list_frame, columns=columns, show="headings", bootstyle="info", selectmode="extended")
        self.messages_list.heading("from", text="Nom")
        self.messages_list.column("from", width=150, anchor="center")
        self.messages_list.heading("subject", text="Sujet")
        self.messages_list.column("subject", width=200, anchor="center")
        self.messages_list.heading("status", text="Statut")
        self.messages_list.column("status", width=120, anchor="center")
        self.messages_list.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        self.messages_list.bind("<<TreeviewSelect>>", self.on_message_select)
        scrollbar_list = tb.Scrollbar(list_frame, orient="vertical", command=self.messages_list.yview)
        self.messages_list.configure(yscrollcommand=scrollbar_list.set)
        scrollbar_list.pack(side=tk.RIGHT, fill=tk.Y)
        
        self.detail_frame = tb.Frame(main_frame)
        self.detail_frame.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        self.detail_frame.grid_rowconfigure(1, weight=1)
        self.detail_frame.grid_columnconfigure(0, weight=1)
        self.detail_frame.grid_propagate(False)
        self.detail_frame.config(width=400, height=400)
        self.no_message_label = tb.Label(self.detail_frame, text="Aucun message sélectionné", font=("Helvetica", 12, "bold"))
        self.no_message_label.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
        self.header_frame = tb.Frame(self.detail_frame, padding=10)
        self.name_label = tb.Label(self.header_frame, text="", font=("Helvetica", 12, "bold"))
        self.name_label.pack(anchor="w")
        self.email_frame = tb.Frame(self.header_frame)
        self.email_frame.pack(anchor="w", pady=5)
        self.email_label = tb.Label(self.email_frame, text="", font=("Helvetica", 12))
        self.email_label.pack(side=tk.LEFT)
        self.copy_btn = tb.Button(self.email_frame, text="Copier", bootstyle="info", command=lambda: None, takefocus=False)
        self.copy_btn.pack(side=tk.LEFT, padx=10)
        self.subject_label = tb.Label(self.header_frame, text="", font=("Helvetica", 12))
        self.subject_label.pack(anchor="w", pady=(5, 0))

        message_frame = tb.Frame(self.detail_frame)
        message_frame.grid(row=1, column=0, sticky="nsew", padx=10, pady=10)
        message_frame.grid_rowconfigure(0, weight=1)
        message_frame.grid_columnconfigure(0, weight=1)
        self.message_text = tk.Text(message_frame, wrap=tk.WORD, font=("Helvetica", 11), state="disabled", padx=10, pady=10)
        self.message_text.grid(row=0, column=0, sticky="nsew")
        scrollbar_text = tb.Scrollbar(message_frame, orient="vertical", command=self.message_text.yview)
        scrollbar_text.grid(row=0, column=1, sticky="ns")
        self.message_text.configure(yscrollcommand=scrollbar_text.set)

    def _setup_bottom_frame(self) -> None:
        btn_frame = tb.Frame(self, padding=10)
        btn_frame.pack(side=tk.BOTTOM, fill=tk.X, padx=15, pady=15)
        refresh_btn = tb.Button(btn_frame, text="Refresh", command=self.load_data, bootstyle="secondary", takefocus=False)
        refresh_btn.pack(side=tk.LEFT, padx=5)
        delete_btn = tb.Button(btn_frame, text="Supprimer", command=self.delete_message, bootstyle="danger", takefocus=False)
        delete_btn.pack(side=tk.LEFT, padx=5)
        select_all_btn = tb.Button(btn_frame, text="Tout sélectionner", command=self.select_all, bootstyle="info", takefocus=False)
        select_all_btn.pack(side=tk.LEFT, padx=5)

    def load_data(self) -> None:
        search_term = self.search_var.get().strip()
        selected_status = self.status_filter_var.get()
        base_query = """
            SELECT
                id,
                first_name,
                last_name,
                email,
                subject,
                IFNULL(status, 'Non lu') AS status,
                message
            FROM contacts
        """
        where_clauses = []
        params = []
        if search_term:
            where_clauses.append("(first_name LIKE %s OR last_name LIKE %s OR subject LIKE %s OR message LIKE %s)")
            like_str = f"%{search_term}%"
            params.extend([like_str] * 4)
        if selected_status != "Tous":
            where_clauses.append("status = %s")
            params.append(selected_status)
        where_str = " WHERE " + " AND ".join(where_clauses) if where_clauses else ""
        query = base_query + where_str + " ORDER BY id DESC"
        try:
            rows = self.db.fetch_all(query, params)
            for item in self.messages_list.get_children():
                self.messages_list.delete(item)
            for row in rows:
                full_name = f"{row['first_name']} {row['last_name']}"
                self.messages_list.insert("", "end", iid=row["id"],
                                          values=(full_name, row["subject"], row["status"]))
            self.update_unread_count()
            self.header_frame.grid_remove()
            self.no_message_label.config(text="Aucun message sélectionné")
            self.no_message_label.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
            self.message_text.config(state="normal")
            self.message_text.delete("1.0", tk.END)
            self.message_text.config(state="disabled")
        except Exception as e:
            my_showerror(self, "Erreur", f"Impossible de charger les données.\n{e}")

    def update_unread_count(self) -> None:
        count = 0
        for child in self.messages_list.get_children():
            if self.messages_list.set(child, "status") == "Non lu":
                count += 1
        self.unread_label.config(text=f"Non lus : {count}")

    def on_message_select(self, event: tk.Event) -> None:
        selected = self.messages_list.selection()
        if not selected:
            self.header_frame.grid_remove()
            self.no_message_label.config(text="Aucun message sélectionné")
            self.no_message_label.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
            self.message_text.config(state="normal")
            self.message_text.delete("1.0", tk.END)
            self.message_text.config(state="disabled")
            return
        if len(selected) > 1:
            self.header_frame.grid_remove()
            self.no_message_label.config(text="Vous avez sélectionné plusieurs messages")
            self.no_message_label.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
            self.message_text.config(state="normal")
            self.message_text.delete("1.0", tk.END)
            self.message_text.config(state="disabled")
            return
        msg_id = selected[0]
        try:
            query = """
                SELECT
                    id,
                    first_name,
                    last_name,
                    email,
                    subject,
                    IFNULL(status, 'Non lu') AS status,
                    message
                FROM contacts
                WHERE id = %s
                LIMIT 1
            """
            row = self.db.fetch_one(query, (msg_id,))
            if row and row['status'] == "Non lu":
                update_query = "UPDATE contacts SET status = 'Lu' WHERE id = %s"
                self.db.execute(update_query, (msg_id,))
                row['status'] = "Lu"
            if row:
                self.no_message_label.grid_remove()
                self.header_frame.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
                full_name = f"{row['first_name']} {row['last_name']}"
                email = row['email']
                subject = row['subject']
                self.name_label.config(text=f"De : {full_name}")
                self.email_label.config(text=f"Email : {email}")
                self.copy_btn.config(command=lambda: self.silent_copy(email))
                self.subject_label.config(text=f"Sujet : {subject}")
                self.message_text.config(state="normal")
                self.message_text.delete("1.0", tk.END)
                self.message_text.insert(tk.END, row['message'])
                self.message_text.config(state="disabled")
                self.messages_list.set(msg_id, column="status", value=row["status"])
                self.update_unread_count()
            else:
                self.header_frame.grid_remove()
                self.no_message_label.config(text="Message introuvable")
                self.no_message_label.grid(row=0, column=0, sticky="nw", padx=10, pady=10)
                self.message_text.config(state="normal")
                self.message_text.delete("1.0", tk.END)
                self.message_text.config(state="disabled")
        except Exception as e:
            my_showerror(self, "Erreur", f"Impossible de récupérer le message.\n{e}")

    def delete_message(self) -> None:
        selected = self.messages_list.selection()
        if not selected:
            my_showinfo(self, "Info", "Veuillez sélectionner un message à supprimer.")
            return
        confirm = my_askyesno(self, "Confirmation", "Voulez-vous vraiment supprimer le(s) message(s) sélectionné(s) ?")
        if not confirm:
            return
        try:
            for item_id in selected:
                delete_query = "DELETE FROM contacts WHERE id = %s"
                self.db.execute(delete_query, (item_id,))
            self.load_data()
            my_showinfo(self, "Succès", "Message(s) supprimé(s) avec succès.")
        except Exception as e:
            my_showerror(self, "Erreur", f"Impossible de supprimer.\n{e}")

    def select_all(self) -> None:
        all_items = self.messages_list.get_children()
        self.messages_list.selection_set(all_items)

    def silent_copy(self, email: str) -> None:
        self.clipboard_clear()
        self.clipboard_append(email)
        self.update()

if __name__ == "__main__":
    app = MainWindow()
    app.mainloop()
