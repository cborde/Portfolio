package com.example.cocoquizz.Score

import android.os.Bundle
import android.widget.ArrayAdapter
import android.widget.ListView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.cocoquizz.Local.QuestionsDataBase
import com.example.cocoquizz.R

class score : AppCompatActivity() {

    lateinit var dataBase: QuestionsDataBase
    lateinit var scores_list : ListView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.scores)

        dataBase = QuestionsDataBase(this)

        var scores = arrayListOf<String>()

        val cursor = dataBase.getAllScores()

        if (cursor.count == 0){
            setContentView(R.layout.activity_main)
            Toast.makeText(this, "Il n'y a pas encore de scores enregistrés", Toast.LENGTH_SHORT).show()
            super.finish()
        } else {

            System.out.println("AZERTYUIOP")

            cursor.moveToFirst()

            while (!cursor.isAfterLast()) {
                scores.add(cursor.getString(1) + " : " + cursor.getString(0))
                cursor.moveToNext()
            }


            setContentView(R.layout.scores)
            val themeAdapter = ArrayAdapter(
                this,
                R.layout.items,
                R.id.theme_name,
                scores
            )

            scores_list = ListView(this)
            setContentView(scores_list)
            scores_list.adapter = themeAdapter
        }
    }

}