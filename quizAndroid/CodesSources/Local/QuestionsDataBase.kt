package com.example.cocoquizz.Local

import android.content.ContentValues
import android.content.Context
import android.database.Cursor
import android.database.sqlite.SQLiteDatabase
import android.database.sqlite.SQLiteOpenHelper

import com.example.cocoquizz.Objets.Question

import java.util.ArrayList

class QuestionsDataBase(context: Context) :
    SQLiteOpenHelper(context, DATABASE_NAME, null, DATABASE_VERSION) {
    internal var db: SQLiteDatabase

    /*
     * Get All Themes
     */
    val cursorTheme: Cursor
        get() {
            this.db = writableDatabase
            return this.db.rawQuery("SELECT * FROM THEME", null)
        }

    /*
     * Get All Questions
     */
    val allQuestions: ArrayList<String>
        get() {
            this.db = writableDatabase
            val cursor = this.db.rawQuery("SELECT texteQuestion FROM QUESTION", null)
            cursor.moveToFirst()
            val qst = ArrayList<String>()
            while (!cursor.isAfterLast) {
                qst.add(cursor.getString(0))
                cursor.moveToNext()
            }
            return qst
        }

    init {
        this.db = this.writableDatabase
    }

    override fun onCreate(database: SQLiteDatabase) {
        this.db = database
        database.execSQL(DATABASE_CREATE_TABLE_THEME)
        database.execSQL(DATABASE_CREATE_TABLE_QUESTION)
        database.execSQL(DATABASE_CREATE_TABLE_PROPOSITION)
        database.execSQL(DATABASE_CREATE_TABLE_SCORE)

        /*
         * At the install of the application, one theme is created to allow user to play directely on quizz without create one
         */
        database.execSQL("INSERT INTO THEME (id_theme, label_theme) VALUES (0, \"Le quizz du chef\")")

        database.execSQL("INSERT INTO QUESTION (id_question, texteQuestion, id_theme, reponseOk_id) VALUES (0, \"Qui est le créateur de Lego ?\", 0, 2)")

        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Hans Beck\", 0)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Ole Kirk Christiansen\", 0)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Niels Lego\", 0)")

        database.execSQL("INSERT INTO QUESTION (id_question, texteQuestion, id_theme, reponseOk_id) VALUES (1, \"Quelle équipe à gagné la coupe du monde de rugby 2015 ?\", 0, 3)")

        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Angleterre\", 1)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Afrique du Sud\", 1)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"Nouvelle Zélande\", 1)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"France\", 1)")


        database.execSQL("INSERT INTO QUESTION (id_question, texteQuestion, id_theme, reponseOk_id) VALUES (2, \"Quelle est la distance entre Paris et Montréal ?\", 0, 1)")

        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"5502\", 2)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"5854\", 2)")
        database.execSQL("INSERT INTO REPONSE (texteReponse, id_question) VALUES (\"6220\", 2)")
    }

    override fun onUpgrade(db: SQLiteDatabase, oldVersion: Int, newVersion: Int) {
        db.execSQL("DROP TABLE IF EXISTS quizz")
        db.execSQL("DROP TABLE IF EXISTS question")
        db.execSQL("DROP TABLE IF EXISTS proposition")
        db.execSQL("DROP TABLE IF EXISTS SCORE")
        onCreate(db)
    }

    /*
     *
     *
     *  GETTERS OF DATA IN DB
     *
     *
     *
     */

    /*
     * Get Cursor of Questions for the one given theme
     */
    fun getCursorForQuestion(quizzTheme: Int): Cursor {
        this.db = writableDatabase
        return this.db.rawQuery("SELECT id_question, texteQuestion, reponseOk_id FROM QUESTION WHERE id_theme=$quizzTheme",null)
    }

    /*
     * Get Cursor of Propositions fot the given question
     */
    fun getCursorForProposition(questionNumber: Int): Cursor {
        this.db = writableDatabase
        return this.db.rawQuery("SELECT * FROM REPONSE where id_question=$questionNumber", null)
    }

    /*
     * Get the next id for the given table
     */
    fun getNextId(table: String): Int {
        val db = this.readableDatabase
        val cursor = db.rawQuery("SELECT seq FROM sqlite_sequence WHERE name=?", arrayOf(table))
        val last = if (cursor.moveToFirst()) cursor.getInt(0) else 0
        cursor.close()
        return last + 1
    }

    /*
     * Get the theme id for the given theme
     */
    fun getThemeId(theme: String): Int {
        this.db = writableDatabase
        val cursor =
            this.db.rawQuery("SELECT id_theme FROM THEME WHERE label_theme = \"$theme\";", null)
        cursor.moveToFirst()
        val t = cursor.getInt(0)
        cursor.close()
        return t
    }

    /*
     * Get the cursor of all themes
     */
    fun getAllTheme(): Cursor {
        this.db = writableDatabase
        val cursor = this.db.rawQuery("SELECT label_theme FROM THEME", null)
        return cursor;
    }

    /*
     * Get the ids of the question for the given theme
     */
    fun getQuestionIdWithTheme(theme_id: Int): ArrayList<Int> {
        this.db = writableDatabase
        val cursor =
            this.db.rawQuery("SELECT id_question FROM QUESTION WHERE id_theme = $theme_id;", null)
        cursor.moveToFirst()
        val questions_id = ArrayList<Int>()
        while (!cursor.isAfterLast) {
            questions_id.add(cursor.getInt(0))
            cursor.moveToNext()
        }
        return questions_id
    }

    /*
     * Get all fields of one question with the given question's id
     */
    fun getOneQuestion(question: String): Question {
        this.db = writableDatabase
        val cursor = this.db.rawQuery(
            "SELECT id_question, texteQuestion, reponseOk_id FROM QUESTION WHERE texteQuestion = \"$question\";",
            null
        )
        cursor.moveToFirst()
        return Question(cursor.getInt(0), cursor.getString(1), cursor.getInt(2))
    }

    /*
     * Get All scores and the players
     */
    fun getAllScores() : Cursor{
        this.db = writableDatabase
        return this.db.rawQuery("SELECT score_, pseudo FROM SCORE", null)
    }

    /*
     * Get All questions for the given theme
     */
    fun getAllQuestionTheme(theme: String): Cursor{
        this.db = writableDatabase
        val id_theme = getThemeId(theme)
        return this.db.rawQuery("SELECT texteQuestion FROM QUESTION WHERE id_theme=$id_theme;", null)
    }

    /*
     * Get one score with the name of the player
     */
    /*
    fun getScoresWithName(pseudo: String) : Cursor{
        this.db = writableDatabase
        return this.db.rawQuery("SELECT score_ FROM SCORE WHERE pseudo = \"$pseudo\";", null)
    }*/

    /*
     *
     *
     *
     *   ADD DATA IN DB
     *
     *
     *
     */

    /*
     * Creation of one theme
     */
    fun creerTheme(id: Int, theme: String): Long {
        this.db = writableDatabase
        val c = ContentValues()
        c.put("id_theme", id)
        c.put("label_theme", theme)
        return db.insert("THEME", null, c)
    }

    /*
     * Creation of one question
     */
    fun creerQuestion(id: Int, question: String, theme_id: Int, reponseOk_id: Int): Long {
        this.db = writableDatabase
        val c = ContentValues()
        c.put("id_question", id)
        c.put("texteQuestion", question)
        c.put("id_theme", theme_id)
        c.put("reponseOk_id", reponseOk_id)
        return db.insert("QUESTION", null, c)
    }

    /*
     * Creation of one answer
     */
    fun creerReponse(id: Int, reponse: String, question_id: Int): Long {
        this.db = writableDatabase
        val c = ContentValues()
        c.put("id_reponse", id)
        c.put("texteReponse", reponse)
        c.put("id_question", question_id)
        return db.insert("REPONSE", null, c)
    }

    /*
     * Add the score and the player in DB
     */
    fun addScore(score: Int, pseudo: String) {
        this.db = writableDatabase
        db.execSQL("INSERT INTO SCORE (score_, pseudo) VALUES ($score, \"$pseudo\");")
    }


    /*
     *
     *
     *
     *    DELETE DATA IN DB
     *
     *
     *
     */

    /*
     * Delete the given theme
     */
    fun supprimerTheme(theme: String) {
        this.db = writableDatabase
        val id_theme = getThemeId(theme)
        db.execSQL("DELETE FROM THEME WHERE label_theme = \"$theme\";")
        supprimerQuestion(id_theme)
    }

    /*
     * Delete all questions of the given theme
     */
    fun supprimerQuestion(theme_id: Int) {
        this.db = writableDatabase
        val qst_id = getQuestionIdWithTheme(theme_id)
        db.execSQL("DELETE FROM QUESTION WHERE id_theme = $theme_id;")
        for (i in qst_id) {
            db.execSQL("DELETE FROM REPONSE WHERE id_question = $i;")
        }
    }

    /*
     *
     *
     *   UPDATE DATA IN DB
     *
     *
     *
     *
     */

    /*
     * Update the name of the given theme
     */
    fun modifierTheme(theme: String, theme_id: Int) {
        this.db = writableDatabase
        db.execSQL("UPDATE THEME SET label_theme = \"$theme\" WHERE id_theme = $theme_id;")
    }

    /*
     * Update the label of one given question
     */
    fun modifierQuestion(id_qst: Int, texte: String) {
        this.db = writableDatabase
        db.execSQL("UPDATE QUESTION SET texteQuestion = \"$texte\" WHERE id_question = $id_qst;")
    }

    /*
     * Update the label of one given answer
     */
    fun modifierReponse(id_rep: Int, texte: String) {
        this.db = writableDatabase
        db.execSQL("UPDATE REPONSE SET texteReponse = \"$texte\" WHERE id_reponse = $id_rep;")
    }

    /*
     *
     *
     *  OBJECT TO CREATE DIFFERENT TABLE
     *
     *          THEME : id, label
     *          QUESTION  : id, label, id_theme, reponseOk_id
     *          PROPOSITION : id, label, id_question
     *          SCORE : id, score, pseudo
     *
     *
     *
     */
    companion object {

        private val DATABASE_CREATE_TABLE_THEME =
            "create table THEME (id_theme integer primary key autoincrement, label_theme text not null);"
        private val DATABASE_CREATE_TABLE_QUESTION =
            "create table QUESTION (id_question integer primary key autoincrement, texteQuestion text not null, id_theme integer not null, reponseOk_id integer not null);"
        private val DATABASE_CREATE_TABLE_PROPOSITION =
            "create table REPONSE (id_reponse integer primary key autoincrement, texteReponse text not null, id_question integer not null);"
        private val DATABASE_CREATE_TABLE_SCORE =
            "create table SCORE (id integer primary key autoincrement, score_ integer not null, pseudo text not null);"

        private val DATABASE_NAME = "questions.db"
        private val DATABASE_VERSION = 1
    }
}