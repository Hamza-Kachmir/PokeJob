import pymysql
from contextlib import contextmanager
from typing import Any, Generator, Optional, List, Dict

class Database:
    """Classe utilitaire pour exécuter des requêtes MySQL via PyMySQL."""
    def __init__(self) -> None:
        """Initialise la configuration de connexion à la BDD Docker."""
        self.config = {
            "host": "127.0.0.1",
            "port": 3307,
            "user": "root",
            "password": "rootpassword",
            "database": "pokejob",
            "charset": "utf8mb4",
            "cursorclass": pymysql.cursors.DictCursor
        }

    @contextmanager
    def get_connection(self) -> Generator[pymysql.connections.Connection, None, None]:
        """Contexte de gestion de la connexion MySQL (ouvre et ferme automatiquement la connexion)."""
        cnx = pymysql.connect(**self.config)
        try:
            yield cnx
        finally:
            cnx.close()

    @contextmanager
    def get_cursor(self, cnx: pymysql.connections.Connection, dict_cursor: bool = True) -> Generator[Any, None, None]:
        """Contexte de gestion du curseur (retourne des dictionnaires si dict_cursor=True)."""
        if dict_cursor:
            cursor = cnx.cursor(pymysql.cursors.DictCursor)
        else:
            cursor = cnx.cursor()
        try:
            yield cursor
        finally:
            cursor.close()

    def fetch_all(self, query: str, params: Optional[List[Any]] = None) -> List[Dict[str, Any]]:
        """Exécute une requête SELECT et retourne toutes les lignes sous forme de dictionnaires."""
        with self.get_connection() as cnx:
            with self.get_cursor(cnx) as cursor:
                cursor.execute(query, params or [])
                return cursor.fetchall()

    def fetch_one(self, query: str, params: Optional[List[Any]] = None) -> Optional[Dict[str, Any]]:
        """Exécute une requête SELECT et retourne une seule ligne sous forme de dictionnaire."""
        with self.get_connection() as cnx:
            with self.get_cursor(cnx) as cursor:
                cursor.execute(query, params or [])
                return cursor.fetchone()

    def execute(self, query: str, params: Optional[List[Any]] = None, commit: bool = True) -> int:
        """Exécute une commande SQL (INSERT, UPDATE, DELETE) et retourne le nombre de lignes affectées."""
        with self.get_connection() as cnx:
            with self.get_cursor(cnx, dict_cursor=False) as cursor:
                cursor.execute(query, params or [])
                if commit:
                    cnx.commit()
                return cursor.rowcount